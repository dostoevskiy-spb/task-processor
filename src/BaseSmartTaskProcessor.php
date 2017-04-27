<?php

namespace dostoevskiy\processor\src;

use dostoevskiy\processor\src\classes\AbstractTask;
use dostoevskiy\processor\src\classes\Listner;
use dostoevskiy\processor\src\classes\ProcessManager;
use dostoevskiy\processor\src\classes\Worker;
use dostoevskiy\processor\src\interfaces\GateProcessorInterface;
use dostoevskiy\processor\src\interfaces\ListnerInterface;
use dostoevskiy\processor\src\interfaces\StorageInterface;
use dostoevskiy\processor\src\interfaces\TaskProcessorInterface;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Object;
use yii\helpers\ArrayHelper;

/**
 *
 * @property array $availableStorageTypes
 * @property array $availableTypes
 */
class BaseSmartTaskProcessor extends Object implements GateProcessorInterface
{
    public $tasksConfig    = [];
    public $storagesConfig = [];
    public $listnerConfig;

    /** @var  ListnerInterface|Listner */
    public static $listner;
    /** @var  AbstractTask[] */
    public static $tasks = [];
    /** @var  StorageInterface|[] */
    public static $storages = [];


    protected $defaultStorageClass = 'dostoevskiy\processor\src\storage\Storage';
    protected $defaultListnerClass = 'dostoevskiy\processor\src\classes\Listner';

    public function listen()
    {
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
                        if (!$task->storage->configureContext($taskName, $task->storageOptions)) {
                            throw new \Exception("Cant configure context of $taskName");
                        }
                }
            }
            foreach (Worker::$servicesToReload as $service) {
                Yii::$app->$service->close();
                Yii::$app->$service->open();
            }
        };

        self::$listner->onMessage = function ($connection, $data) {
            /** @var $connection \Workerman\Connection\ConnectionInterface */
            $body     = explode("\r\n", $data);
            $body     = json_decode(array_pop($body), true, 1024);
            $taskName = ArrayHelper::getValue($body, 'task', false);
            $taskData = ArrayHelper::getValue($body, 'data', false);
            if (!$taskName) {
                $connection->send(json_encode(['status' => 'error', 'error' => 'Task directive is missing']));
                $connection->close();

                return;
            }
            if (!$taskData) {
                $connection->send(json_encode(['status' => 'error', 'error' => 'Task data is missing']));
                $connection->close();

                return;
            }
            /** @var AbstractTask $taskInstance */
            $taskInstance = ArrayHelper::getValue(self::$tasks, $taskName);
            if (!$taskInstance) {
                $connection->send(json_encode(['status' => 'error', 'error' => "Unknown task $taskName"]));
                $connection->close();

                return;
            }
            $resp   = json_encode(['status' => 'success']);
            $length = strlen($resp);
            if ($taskInstance->isLive()) {
                if ($taskInstance->isTransactional()) {
                    $taskInstance->process($taskData);
                    $connection->send("HTTP/1.1 200 OK\r\nServer: workerman\r\nContent-Length: $length\r\nContent-Type: application/json\r\n\r\n" . $resp);
                    $connection->close();

                    return;
                } else {
                    $connection->send("HTTP/1.1 200 OK\r\nServer: workerman\r\nContent-Length: $length\r\nContent-Type: application/json\r\n\r\n" . $resp);
                    $connection->close();
                    $taskInstance->process($data);

                    return;
                }
            } else {
                $storageInstance = $taskInstance->storage;
                if ($taskInstance->isTransactional()) {
                    $storageInstance->push($taskName, $taskData);
                    $connection->send("HTTP/1.1 200 OK\r\nServer: workerman\r\nContent-Length: $length\r\nConnection: keep-alive\r\nContent-Type: application/json\r\n\r\n" . $resp);
                    $connection->close();


                    return;
                } else {
                    $connection->send("HTTP/1.1 200 OK\r\nServer: workerman\r\nContent-Length: $length\r\nConnection: keep-alive\r\nContent-Type: application/json\r\n\r\n" . $resp);
                    $connection->close();
                    $storageInstance->push($taskName, $taskData);

                    return;
                }
            }
        };
        self::$listner->onConnect = function ($connection) {
//            echo "New Connection\n";
        };
        self::$listner->listen();
    }

    /**
     * @return AbstractTask[]
     */
    public static function getTasks()
    {
        return self::$tasks;
    }


    public function run($taskName)
    {
        if (!array_key_exists($taskName, self::$tasks)) {
            throw new InvalidConfigException("invalid task $taskName");
        }
        self::$listner->name = $taskName;
        /** @var AbstractTask $task */
        $task                         = self::$tasks[$taskName];
        self::$listner->threads = $task->threads;
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

    public function process()
    {
        $processManager = new ProcessManager(['processor' => $this]);
        $processManager->manage();
    }

    public function init()
    {
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
        $config        = ArrayHelper::merge(['class' => $this->defaultListnerClass], $this->listnerConfig);
        $requestParams = Yii::$app->request->params;
        $run           = array_shift($requestParams);
        $mode          = strpos($run, 'task-processor-run') === false;
        self::$listner = Yii::createObject(ArrayHelper::merge($config, ['mode' => $mode ? Listner::MODE_LISTEN : Listner::MODE_PROCESS]));
        if (!self::$listner instanceof ListnerInterface) {
            throw new InvalidConfigException('Listner must implements ListnerInterface');
        }
    }
}