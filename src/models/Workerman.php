<?php
namespace dostoevskiy\processor\src\models;

use Yii;

/**
 * This is the model class for table "{{%workerman}}".
 *
 * @property integer $workerman_id
 * @property integer $pid
 * @property string $name
 * @property integer $create_timestamp
 */
class Workerman extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%workerman}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['pid'], 'required'],
            [['pid', 'create_timestamp'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['create_timestamp'], 'default', 'value' => time()],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'workerman_id' => 'Workerman ID',
            'pid' => '进程ID',
            'name' => '名称',
            'create_timestamp' => '创建时间戳',
        ];
    }

    /**
     * @inheritdoc
     * @return WorkermanQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new WorkermanQuery(get_called_class());
    }
}