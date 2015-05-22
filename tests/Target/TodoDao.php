<?php

namespace Omelet\Tests\Target;

use Omelet\Annotation\Dao;
use Omelet\Annotation\Select;
use Omelet\Annotation\Insert;
use Omelet\Annotation\Update;
use Omelet\Annotation\Delete;

use Omelet\Annotation\ParamAlt;

/**
 * @Dao
 */

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
     *
     * @param \DateTime from
     * @param \DateTime to
     */
    function listByPub(\DateTime $from, \DateTime $to);
    
    /**
     * @Insert
     *
     * @param string[] fields
     */
    function insert(array $fields);
    
    /**
     * @Update
     *
     * @param string[] fields
     */
    function update(array $fields);
    
    /**
     * @Delete
     *
     * @param int id
     */
    function delete($id);
}
