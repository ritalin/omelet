<?php

namespace Omelet\Tests\Target;

use Omelet\Annotation\Entity;
use Omelet\Annotation\Column;

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
    
    /**
     * @param calable(Todo -> Void) fn
     */
    public function __construct(callable $fn = null) {
        if ($fn !== null) {
            $fn($this);
        }
    }
    
    public static function __set_state($values) {
        return new Todo(function($obj) use($values) {
            $obj->id = $values['id'];
            $obj->todo = $values['todo'];
            $obj->created = $values['created'];
            $obj->hidden = $values['hidden'];
        });
    }
}
