<?php

namespace dostoevskiy\processor\src\models;

use Yii;

/**
 * This is the model class for collection "process_manager".
 *
 * @property \MongoDB\BSON\ObjectID|string $_id
 * @property mixed $pid
 * @property mixed $cpu
 * @property mixed $resident
 * @property mixed $virtual
 * @property mixed $timestamp
 */
class ProcessManager extends \yii\mongodb\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function collectionName()
    {
        return ['mongo', 'process_manager'];
    }

    /**
     * @return \yii\mongodb\Connection the MongoDB connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('mongo');
    }

    /**
     * @inheritdoc
     */
    public function attributes()
    {
        return [
            '_id',
            'pid',
            'cpu',
            'resident',
            'virtual',
            'timestamp',
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['pid', 'cpu', 'resident', 'virtual', 'timestamp'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            '_id' => 'ID',
            'pid' => 'Pid',
            'cpu' => 'Cpu',
            'resident' => 'Resident',
            'virtual' => 'Virtual',
            'timestamp' => 'Timestamp',
        ];
    }
}
