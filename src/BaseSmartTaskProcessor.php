<?php

namespace dostoevskiy\processor\src;

use dostoevskiy\processor\src\classes\AbstractTask;
use dostoevskiy\processor\src\classes\Listner;
use dostoevskiy\processor\src\classes\ProcessManager;
use dostoevskiy\processor\src\classes\Worker;
use dostoevskiy\processor\src\interfaces\GateProcessorInterface;
use dostoevskiy\processor\src\interfaces\ListnerInterface;
use dostoevskiy\processor\src\interfaces\RequestProtocolInterface;
use dostoevskiy\processor\src\interfaces\StorageInterface;
use dostoevskiy\processor\src\interfaces\TaskProcessorInterface;
use Yii;
use yii\base\Application;
use yii\base\BootstrapInterface;
use yii\base\InvalidConfigException;
use yii\base\Object;
use yii\helpers\ArrayHelper;

/**
 *
 * @property array $availableStorageTypes
 * @property array $availableTypes
 */
class BaseSmartTaskProcessor extends Object implements GateProcessorInterface, BootstrapInterface {

	public $tasksConfig           = [];
	public $requestProtocolConfig = [];
	public $storagesConfig        = [];
	public $listnerConfig;

	/** @var  ListnerInterface|Listner */
	public static $listner;
	/** @var  AbstractTask[] */
	public static $tasks = [];
	/** @var  StorageInterface|[] */
	public static $storages = [];
	/** @var  RequestProtocolInterface */
	public static $requestProtocol;


	protected $defaultStorageClass          = 'dostoevskiy\processor\src\storage\Storage';
	protected $defaultListnerClass          = '';
	protected $defaultRequestProtocolConfig = [
		'class' => 'dostoevskiy\processor\src\protocols\DefaultRequestProtocol'
	];

	public function listen() {
		self::$listner->name          = 'SmartTaskProcessorListner';
		self::$listner->onWorkerStart = function () {
			/**
			 * @var                  $name
			 * @var StorageInterface $storage
			 */
			foreach (self::$storages as $name => $storage) {
				if (!$storage->configureConnection()) {
					throw new \Exception("Cant connect to $name");
				}
				/** @var AbstractTask $task */
				foreach (self::$tasks as $taskName => $task) {
					if (!$task->isLive()) {
						if (!$task->storage->configureContext($taskName, $task->storageOptions)) {
							throw new \Exception("Cant configure context of $taskName");
						}
					}
				}
			}
			foreach (Worker::$servicesToReload as $service) {
				Yii::$app->$service->close();
				Yii::$app->$service->open();
			}
		};
		self::$listner->onMessage = self::$requestProtocol->getProcessRequestCallback(self::$tasks);
		self::$listner->onConnect = function ($connection) {
			//            echo "New Connection\n";
		};
		self::$listner->listen();
	}

	/**
	 * @return AbstractTask[]
	 */
	public static function getTasks() {
		return self::$tasks;
	}


	public function run($taskName) {
		if (!array_key_exists($taskName, self::$tasks)) {
			throw new InvalidConfigException("invalid task $taskName");
		}
		self::$listner->name = $taskName;
		/** @var AbstractTask $task */
		$task                         = self::$tasks[$taskName];
		self::$listner->threads       = $task->threads;
		self::$listner->onWorkerStart = function ($task) use ($task, $taskName) {
			if (!$task->storage->configureConnection()) {
				throw new \Exception("Cant connect to {$task->storage->name}");
			}
			/** @var AbstractTask $task */
			if (!$task->storage->configureContext($taskName, $task->storageOptions)) {
				throw new \Exception("Cant configure context for {$task->storage->name}");
			}
			foreach (Worker::$servicesToReload as $service) {
				Yii::$app->$service->close();
				Yii::$app->$service->open();
			}
			$task->storage->loop([$task, 'process'], $taskName);
		};
		self::$listner->listen();
	}

	public function process() {
		$processManager = new ProcessManager(['processor' => $this]);
		$processManager->manage();
	}

	public function init() {
		/* Create storages instances */
		foreach ($this->storagesConfig as $name => $storage) {
			$class    = ArrayHelper::getValue($storage, 'class', $this->defaultStorageClass);
			$settings = ArrayHelper::merge(['class' => $class], $storage);
			$storage  = Yii::createObject($settings);
			if (!$storage instanceof StorageInterface) {
				throw new InvalidConfigException('Storage must implements of StorageInterface');
			}
			$storage->name         = $name;
			self::$storages[$name] = $storage;
		}
		/* Create tasks instances */
		foreach ($this->tasksConfig as $name => $task) {
			$task = Yii::createObject($task);
			if (!$task instanceof AbstractTask) {
				throw new InvalidConfigException('Task must be instance of AbstractTask');
			}
			self::$tasks[$name] = $task;
		}
		/* Create listner instace */
		$config                = ArrayHelper::merge(['class' => $this->defaultListnerClass], $this->listnerConfig);
		$requestParams         = Yii::$app->request->params;
		$run                   = array_shift($requestParams);
		$mode                  = strpos($run, 'task-processor-run') === FALSE;
		self::$requestProtocol = $this->requestProtocolConfig ? Yii::createObject($this->requestProtocolConfig) : Yii::createObject($this->defaultRequestProtocolConfig);
		self::$listner         = Yii::createObject(ArrayHelper::merge($config, ['mode' => $mode ? Listner::MODE_LISTEN : Listner::MODE_PROCESS]));
		if (!self::$listner instanceof ListnerInterface) {
			throw new InvalidConfigException('Listner must implements ListnerInterface');
		}
	}

	/**
	 * Bootstrap method to be called during application bootstrap stage.
	 *
	 * @param Application $app the application currently running
	 */
	public function bootstrap($app) {
//		$bootstrapper = new Bootstrapper();
//		Yii::$app->
		//		Yii::$app->migrate->
		$controllerMap = Yii::$app->controllerMap;
		$migrate       = $controllerMap['migrate'];
		if (is_array($migrate['migrationNamespaces'])) {
			$migrate['migrationNamespaces'][] = 'dostoevskiy\processor\src\migrations';
		} else {
			$migrate['migrationNamespaces'] = ['dostoevskiy\processor\src\migrations'];
		}
		$controllerMap['migrate'] = $migrate;
		Yii::$app->controllerMap = $controllerMap;

		Yii::$app->set('restRequest',  [
			'class'     => 'dostoevskiy\processor\src\components\RestRequest',
			'scriptUrl' => '/index.php'
		]);
	}
}