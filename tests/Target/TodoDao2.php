<?php

namespace Omelet\Tests\Target;

use Omelet\Annotation\Select;
use Omelet\Annotation\Insert;

use Omelet\Annotation\ParamAlt;
use Omelet\Annotation\Returning;

interface TodoDao2 {
    /**
     * @Select
     * @ParamAlt(type="\Omelet\Tests\Target\PrimaryKey", name="key")
     *
     * @param PrimaryKey key
     */
    function listById(PrimaryKey $key);
    
    /**
     * @Select
     * @Returning(type="\Omelet\Tests\Target\Todo[]")
     *
     * @return Todo[] 
     */
    function listAll();
    
    /**
     * @Select
     * @Returning(type="string[]")
     *
     * @return array
     */
    function listAllAsRawArray();
    
    /**
     * @Select
     * @ParamAlt(type="\DateTime", name="from")
     * @ParamAlt(type="\DateTime", name="to")
     * @Returning(type="\Omelet\Tests\Target\Todo[]")
     *
     * @param DateTime from
     * @param DateTime to
     * @return Todo[] 
     */
    function listByPub(\DateTime $from, \DateTime $to);

    /**
     * @Select
     * @ParamAlt(type="\Omelet\Tests\Target\PrimaryKey", name="key")
     * @Returning(type="\Omelet\Tests\Target\Todo")
     *
     * @param PrimaryKey key
     * return Todo
     */
    function findById(PrimaryKey $key);
    
    /**
     * @Select
     * @ParamAlt(type="int", name="id")
     * @Returning(type="bool")
     *
     * @param int key
     * return bool
     */
    function exists($id);
    
    /**
     * @Select
     * @ParamAlt(type="int", name="id")
     * @Returning(type="\Omelet\Tests\Target\Existance")
     *
     * @param int key
     * return Existance
     */
    function existsAsDomain($id);
    
    /**
     * @Select
     * @Returning(type="int[]")
     *
     * return int[]
     */
    function primaryKeysDesc();
    
    /**
     * @Select
     * @Returning(type="\Omelet\Tests\Target\PrimaryKey[]")
     *
     * return PrimaryKey[]
     */
    function primaryKeysDescAsDomain();
    
    /**
     * @Insert
     * @ParamAlt(type="\Omelet\Tests\Target\Todo", name="entity")
     *
     * @param Todo entity
     */
    function insert(Todo $entity);
}
