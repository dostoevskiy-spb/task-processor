<?php

namespace dostoevskiy\processor\src\classes;

use dostoevskiy\processor\src\models\Workerman;
use Yii;
use yii\db\Exception;

class Worker extends \Workerman\Worker
{
    public static $servicesToReload = [];

    public $name = 'StatsWorker';
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
            if (!$workermanModel->save()) {
                foreach ($workermanModel->errors as $field) {
                    foreach ($field as $message) {
                        throw new Exception('can not save pid to ' . $message);
                    }
                }
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