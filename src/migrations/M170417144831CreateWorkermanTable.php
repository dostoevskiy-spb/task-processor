<?php
namespace dostoevskiy\processor\src\migrations;
use yii\db\Migration;

class M170417144831CreateWorkermanTable extends Migration
{
    public function safeUp()
    {
        $sql = <<<SQL
CREATE TABLE {{%workerman}} (
 `workerman_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Workerman ID',
 `pid` int(11) unsigned NOT NULL COMMENT 'PID',
 `name` varchar(255) DEFAULT '' COMMENT 'Name',
 `create_timestamp` int(10) unsigned DEFAULT '0' COMMENT 'Timestamp',
 PRIMARY KEY (`workerman_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='workerman_status';
SQL;
        $this->execute($sql);
    }

    public function safeDown()
    {
    }
}
