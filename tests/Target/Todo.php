<?php

namespace Omelet\Tests\Target;

use Omelet\Annotation\Entity;
use Omelet\Annotation\Column;

/**
 * @Entity
 */
class Todo {
    /**
     * @var integer
     * @Column(type="integer", name="id")
     */
    public $id;
    
    /**
     * @var string
     * @Column(type="string", name="todo")
     */
    public $todo;
    
    /**
     * @var \DateTime
     * @Column(type=\DateTime::class, name="created")
     */
    public $created;
    
    /**
     * @var Hidden
     * @Column(type=Hidden::class, name="hidden")
     */
    public $hidden;
    
    public static function __set_state($values) {
        $obj = new Todo;
        
        $obj->id = $values['id'];
        $obj->todo = $values['todo'];
        $obj->created = $values['created'];
        $obj->hidden = $values['hidden'];
        
        return $obj;
    }
}
