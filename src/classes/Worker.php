<?php

namespace dostoevskiy\processor\src\classes;

use Yii;

class Worker extends \Workerman\Worker
{
    public static $servicesToReload = [];
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
            foreach(self::$servicesToReload as $service) {
                Yii::$app->$service->close();
                Yii::$app->$service->open();
            }
            /*$workermanModel = new Workerman();
            $workermanModel->pid = self::$_masterPid;
            $workermanModel->name = $worker->name;
            if (!$workermanModel->save()) {
                foreach ($workermanModel->errors as $field) {
                    foreach ($field as $message) {
                        throw new Exception('can not save pid to ' . $message);
                    }
                }
            }*/
        }
    }
}