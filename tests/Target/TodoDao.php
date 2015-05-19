<?php

namespace Omelet\Tests\Target;

use Omelet\Annotation\Select;
use Omelet\Annotation\Insert;
use Omelet\Annotation\Update;
use Omelet\Annotation\Delete;

use Omelet\Annotation\ParamAlt;

interface TodoDao {
    /**
     * @Select
     */
    function listAll();
    /**
     * @Select
     */
    function listById($id);
    /**
     * @Select
     * @ParamAlt(type=\DateTime::class, name="from")
     * @ParamAlt(type=\DateTime::class, name="to")
     * @param \DateTime from
     * @param \DateTime to
     */
    function listByPub(\DateTime $from, \DateTime $to);
    
    /**
     * @Insert
     * @ParamAlt(type="string[]", name="fields")
     * @param string[] fields
     */
    function insert(array $fields);
    
    /**
     * @Update
     * @ParamAlt(type="string[]", name="fields")
     * @param string[] fields
     */
    function update(array $fields);
    
    /**
     * @Delete
     * @ParamAlt(type="int", name="id")
     * @param int id
     */
    function delete($id);
}
