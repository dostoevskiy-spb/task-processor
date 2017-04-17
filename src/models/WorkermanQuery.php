<?php
namespace dostoevskiy\processor\src\models;

/**
 * This is the ActiveQuery class for [[Workerman]].
 *
 * @see Workerman
 */
class WorkermanQuery extends \yii\db\ActiveQuery
{

    /**
     * Find by `workerman_id`
     *
     * @param string $workermanId [[workerman_id]]
     * @return $this
     */
    public function findByWorkermanId($workermanId)
    {
        $this->andWhere([
                            Workerman::tableName() . '.[[workerman_id]]' => $workermanId,
                        ]);
        return $this;
    }

    /**
     * Find by like `workerman_id`
     *
     * @param string $workermanId [[workerman_id]]
     * @return $this
     */
    public function findByLikeWorkermanId($workermanId)
    {
        $this->andWhere([
                            'like', Workerman::tableName() . '.[[workerman_id]]', $workermanId,
                        ]);
        return $this;
    }

    /**
     * Find by like left `workerman_id`
     *
     * @param string $workermanId [[workerman_id]]
     * @return $this
     */
    public function findByLeftLikeWorkermanId($workermanId)
    {
        $this->andWhere([
                            'like', Workerman::tableName() . '.[[workerman_id]]', '%' . $workermanId, false
                        ]);
        return $this;
    }

    /**
     * Find by like right `workerman_id`
     *
     * @param string $workermanId [[workerman_id]]
     * @return $this
     */
    public function findByRightLikeWorkermanId($workermanId)
    {
        $this->andWhere([
                            'like', Workerman::tableName() . '.[[workerman_id]]', $workermanId . '%', false
                        ]);
        return $this;
    }

    /**
     * Find by `pid`
     *
     * @param string $pid [[pid]]
     * @return $this
     */
    public function findByPid($pid)
    {
        $this->andWhere([
                            Workerman::tableName() . '.[[pid]]' => $pid,
                        ]);
        return $this;
    }

    /**
     * Find by like `pid`
     *
     * @param string $pid [[pid]]
     * @return $this
     */
    public function findByLikePid($pid)
    {
        $this->andWhere([
                            'like', Workerman::tableName() . '.[[pid]]', $pid,
                        ]);
        return $this;
    }

    /**
     * Find by like left `pid`
     *
     * @param string $pid [[pid]]
     * @return $this
     */
    public function findByLeftLikePid($pid)
    {
        $this->andWhere([
                            'like', Workerman::tableName() . '.[[pid]]', '%' . $pid, false
                        ]);
        return $this;
    }

    /**
     * Find by like right `pid`
     *
     * @param string $pid [[pid]]
     * @return $this
     */
    public function findByRightLikePid($pid)
    {
        $this->andWhere([
                            'like', Workerman::tableName() . '.[[pid]]', $pid . '%', false
                        ]);
        return $this;
    }

    /**
     * Find by `name`
     *
     * @param string $name [[name]]
     * @return $this
     */
    public function findByName($name)
    {
        $this->andWhere([
                            Workerman::tableName() . '.[[name]]' => $name,
                        ]);
        return $this;
    }

    /**
     * Find by like `name`
     *
     * @param string $name [[name]]
     * @return $this
     */
    public function findByLikeName($name)
    {
        $this->andWhere([
                            'like', Workerman::tableName() . '.[[name]]', $name,
                        ]);
        return $this;
    }

    /**
     * Find by like left `name`
     *
     * @param string $name [[name]]
     * @return $this
     */
    public function findByLeftLikeName($name)
    {
        $this->andWhere([
                            'like', Workerman::tableName() . '.[[name]]', '%' . $name, false
                        ]);
        return $this;
    }

    /**
     * Find by like right `name`
     *
     * @param string $name [[name]]
     * @return $this
     */
    public function findByRightLikeName($name)
    {
        $this->andWhere([
                            'like', Workerman::tableName() . '.[[name]]', $name . '%', false
                        ]);
        return $this;
    }

    /**
     * Find by `create_timestamp`
     *
     * @param string $createTimestamp [[create_timestamp]]
     * @return $this
     */
    public function findByCreateTimestamp($createTimestamp)
    {
        $this->andWhere([
                            Workerman::tableName() . '.[[create_timestamp]]' => $createTimestamp,
                        ]);
        return $this;
    }

    /**
     * Find by like `create_timestamp`
     *
     * @param string $createTimestamp [[create_timestamp]]
     * @return $this
     */
    public function findByLikeCreateTimestamp($createTimestamp)
    {
        $this->andWhere([
                            'like', Workerman::tableName() . '.[[create_timestamp]]', $createTimestamp,
                        ]);
        return $this;
    }

    /**
     * Find by like left `create_timestamp`
     *
     * @param string $createTimestamp [[create_timestamp]]
     * @return $this
     */
    public function findByLeftLikeCreateTimestamp($createTimestamp)
    {
        $this->andWhere([
                            'like', Workerman::tableName() . '.[[create_timestamp]]', '%' . $createTimestamp, false
                        ]);
        return $this;
    }

    /**
     * Find by like right `create_timestamp`
     *
     * @param string $createTimestamp [[create_timestamp]]
     * @return $this
     */
    public function findByRightLikeCreateTimestamp($createTimestamp)
    {
        $this->andWhere([
                            'like', Workerman::tableName() . '.[[create_timestamp]]', $createTimestamp . '%', false
                        ]);
        return $this;
    }

    /**
     * @inheritdoc
     * @return Workerman[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return Workerman|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

}