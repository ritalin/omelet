<?php

namespace Omelet\Tests\Target;

use Omelet\Annotation\Dao;
use Omelet\Annotation\ParamAlt;
use Omelet\Annotation\Returning;

use Omelet\Annotation\Insert;
use Omelet\Annotation\Select;

/**
 * @Dao(route="/")
 */
interface TodoDao2
{
    /**
     * @Select
     *
     * @param PrimaryKey key
     */
    public function listById(PrimaryKey $key);

    /**
     * @Select
     *
     * @return Todo[]
     */
    public function listAll();

    /**
     * @Select
     *
     * @return array
     */
    public function listAllAsRawArray();

    /**
     * @Select
     *
     * @return integer
     */
    public function listAllReturningTopLeft();

    /**
     * @Select
     *
     * @param DateTime from
     * @param DateTime to
     *
     * @return Todo[]
     */
    public function listByPub(\DateTime $from, \DateTime $to);

    /**
     * @Select
     *
     * @param PrimaryKey key
     *
     * @return Todo
     */
    public function findById(PrimaryKey $key);

    /**
     * @Select
     *
     * @param PrimaryKey key
     *
     * @return Todo
     */
    public function findByIdReturningEntityWithDomain(PrimaryKey $key);

    /**
     * @Select
     *
     * @param PrimaryKey key
     *
     * @return Todo
     */
    public function findByIdReturningAlias(PrimaryKey $key);

    /**
     * @Select
     *
     * @param PrimaryKey key
     *
     * @return Todo
     */
    public function findByIdReturningEditorKeyOnly(PrimaryKey $key);

    /**
     * @Select
     *
     * @param PrimaryKey key
     *
     * @return Todo
     */
    public function findByIdReturningEditor(PrimaryKey $key);

    /**
     * @Select
     *
     * @param int key
     *
     * @return bool
     */
    public function exists($id);

    /**
     * @Select
     *
     * @param int key
     *
     * @return Existance
     */
    public function existsAsDomain($id);

    /**
     * @Select
     *
     * @return int[]
     */
    public function primaryKeysDesc();

    /**
     * @Select
     *
     * @return PrimaryKey[]
     */
    public function primaryKeysDescAsDomain();

    /**
     * @Insert
     *
     * @param Todo entity
     */
    public function insert(Todo $entity);
}
