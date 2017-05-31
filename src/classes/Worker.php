<?php

namespace dostoevskiy\processor\src\classes;

use dostoevskiy\processor\src\models\Workerman;
use Workerman\Lib\Timer;
use Yii;
use yii\db\Exception;

class Worker extends \Workerman\Worker
{
    public static $servicesToReload = [];

    public $name = 'StatsWorker';

    /**
     * Run all worker instances.
     *
     * @return void
     */
    public static function runAll()
    {
        self::checkSapiEnv();
        self::init();
        self::parseCommand();
        self::daemonize();
        self::initWorkers();
        self::installSignal();
        self::saveMasterPid();
        self::forkWorkers();
        self::displayUI();
        self::resetStd();
        self::monitorWorkers();
    }

    /**
     * Init.
     *
     * @return void
     */
    protected static function init()
    {
        // Start file.
        global $argv;

        self::$_startFile = $argv[0];

        // Pid file.
        if (empty(self::$pidFile)) {
            self::$pidFile = __DIR__ . "/../" . str_replace('/', '_', self::$_startFile) . ".pid";
        }

        // Log file.
        if (empty(self::$logFile)) {
            self::$logFile = __DIR__ . '/../workerman.log';
        }
        $log_file = (string)self::$logFile;
        if (!is_file($log_file)) {
            touch($log_file);
            chmod($log_file, 0622);
        }

        // State.
        self::$_status = self::STATUS_STARTING;

        // For statistics.
        self::$_globalStatistics['start_timestamp'] = time();
        self::$_statisticsFile                      = sys_get_temp_dir() . '/workerman.status';

        // Process title.
        self::setProcessTitle('WorkerMan: master process  start_file=' . self::$_startFile);

        // Init data for worker id.
        self::initId();

        // Timer init.
        Timer::init();
    }
    /**
     * Log.
     *
     * @param string $msg
     * @return void
     */
    public static function log($msg)
    {
        $msg = $msg . "\n";
        if (!self::$daemonize) {
            self::safeEcho($msg);
        }

        Yii::info('pid:'. posix_getpid() . ' ' . $msg, 'workerman');
        file_put_contents((string)self::$logFile, date('Y-m-d H:i:s') . ' ' . 'pid:'. posix_getpid() . ' ' . $msg, FILE_APPEND | LOCK_EX);
    }


    /**
     * Save pid.
     *
     * @throws Exception
     */
    protected static function saveMasterPid()
    {
        self::$_masterPid = posix_getpid();

        foreach (self::$_workers as $worker) {
            $workermanModel = new Workerman();
            $workermanModel->pid = self::$_masterPid;
            $workermanModel->name = $worker->name;
            try {
                if (!$workermanModel->save()) {
                    foreach ($workermanModel->errors as $field) {
                        foreach ($field as $message) {
//                        throw new Exception('can not save pid to ' . $message);
                        }
                    }
                }
            } catch(\Exception $e) {

            }
        }
    }

    public function setServicesToReload($services)
    {
        self::$servicesToReload = $services;

        return $this;
    }

    /**
     * Workerman model
     *
     * @param string $name 名称
     * @return array|null|Workerman
     */
    protected static function getWorkermanByName($name)
    {
        Yii::$app->db->close();
        Yii::$app->db->open();
        return Workerman::find()->findByName($name)->one();
    }
}