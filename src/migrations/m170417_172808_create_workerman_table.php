<?php

use yii\db\Migration;

class m170417_172808_create_workerman_table extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
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
        $this->dropTable('{{%workerman}}');
    }
}