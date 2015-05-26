<?php

namespace OmeletTests;

use Omelet\Annotation\AnnotationConverter;
use Omelet\Annotation\Returning;
use Omelet\Annotation\ParamAlt;
use Omelet\Annotation\ColumnType;

use Omelet\Annotation\Entity;
use Omelet\Annotation\Select;
use Omelet\Annotation\Update;

use Doctrine\Common\Annotations\AnnotationException;

class CommentToAnnotationTest extends \PHPUnit_Framework_TestCase {
    /**
     * @test
     */
     public function test_parse_no_doc_comment() {
        $comment = "
            /**
             * foo, bar, baz
             */
        ";
        
        $uses = [
            'Omelet\Tests\Target',
            'Omelet\Annotation',
            __NAMESPACE__,
            
        ];
        $factory = new AnnotationConverter($uses);
        $annotations = $factory->parse($comment, '');
        
        $this->assertCount(0, $annotations['params']);
        $this->assertCount(0, $annotations['returns']);
     }
     
    /**
     * @test
     */
     public function test_parse_no_return_comment() {
        $comment = "
            /**
             * @Select
             *
             * foo, bar, baz
             */
        ";
        
        $uses = [
            'Omelet\Tests\Target',
            'Omelet\Annotation',
            __NAMESPACE__,
            
        ];
        $factory = new AnnotationConverter($uses);
        $annotations = $factory->parse($comment, '');
        
        $this->assertCount(0, $annotations['params']);
        $this->assertCount(0, $annotations['returns']);
     }
     
    /**
     * @test
     */
     public function test_parse_return_primitive() {
        $comment = '
            /**
             * @Select
             *
             * foo, bar, baz
             *
             * @return boolean
             */
        ';
        
        $uses = [
            'Omelet\Tests\Target',
            'Omelet\Annotation',
            __NAMESPACE__,
            
        ];
        $factory = new AnnotationConverter($uses);
        $annotations = $factory->parse($comment, '');
        
        $this->assertCount(0, $annotations['params']);

        $this->assertCount(1, $annotations['returns']);
        $this->assertInstanceOf(Returning::class, $annotations['returns'][0]);
        $this->assertEquals('boolean', $annotations['returns'][0]->type);
     }
     
    /**
     * @test
     */
     public function test_parse_return_primitive_array() {
        $comment = '
            /**
             * @Select
             *
             * foo, bar, baz
             *
             * @return boolean[]
             */
        ';
        
        $uses = [
            'Omelet\Tests\Target',
            'Omelet\Annotation',
            __NAMESPACE__,
            
        ];
        $factory = new AnnotationConverter($uses);
        $annotations = $factory->parse($comment, '');
        
        $this->assertCount(0, $annotations['params']);

        $this->assertCount(1, $annotations['returns']);
        $this->assertInstanceOf(Returning::class, $annotations['returns'][0]);
        $this->assertEquals('boolean[]', $annotations['returns'][0]->type);
     }
     
    /**
     * @test
     */
     public function test_parse_return_primitive_alias() {
        $comment = "
            /**
             * @Select
             *
             * foo, bar, baz
             *
             * @return int
             */
        ";
        
        $lexer = new \Doctrine\Common\Annotations\DocLexer;
        $lexer->setInput($comment);
        
        $tokens = [];
        
        while ($lexer->moveNext()) {
            $tokens[] = $lexer->lookahead;
        }
     }
     
    /**
     * @test
     */
     public function test_parse_return_builtin_class() {
        $comment = '
            /**
             * @Select
             *
             * foo, bar, baz
             *
             * @return DateTime
             */
        ';
        
        $uses = [
            'Omelet\Tests\Target',
            'Omelet\Annotation',
            __NAMESPACE__,
            
        ];
        $factory = new AnnotationConverter($uses);
        $annotations = $factory->parse($comment, '');
        
        $this->assertCount(0, $annotations['params']);

        $this->assertCount(1, $annotations['returns']);
        $this->assertInstanceOf(Returning::class, $annotations['returns'][0]);
        $this->assertEquals(\DateTime::class, $annotations['returns'][0]->type);
     }
     
    /**
     * @test
     */
     public function test_parse_return_custom_class() {
        $comment = '
            /**
             * @Select
             *
             * foo, bar, baz
             *
             * @return Entity
             */
        ';
        
        $uses = [
            'Omelet\Tests\Target',
            'Omelet\Annotation',
            __NAMESPACE__,
            
        ];
        $factory = new AnnotationConverter($uses);
        $annotations = $factory->parse($comment, '');
        
        $this->assertCount(0, $annotations['params']);

        $this->assertCount(1, $annotations['returns']);
        $this->assertInstanceOf(Returning::class, $annotations['returns'][0]);
        $this->assertEquals(Entity::class, $annotations['returns'][0]->type);
     }
     
    /**
     * @test
     */
     public function test_parse_return_custom_class_array() {
        $comment = '
            /**
             * @Select
             *
             * foo, bar, baz
             *
             * @return Entity[]
             */
        ';
        
        $uses = [
            'Omelet\Tests\Target',
            'Omelet\Annotation',
            __NAMESPACE__,
            
        ];
        $factory = new AnnotationConverter($uses);
        $annotations = $factory->parse($comment, '');
        
        $this->assertCount(0, $annotations['params']);

        $this->assertCount(1, $annotations['returns']);
        $this->assertInstanceOf(Returning::class, $annotations['returns'][0]);
        $this->assertEquals(Entity::class . '[]', $annotations['returns'][0]->type);
     }
     
    /**
     * @test
     */
     public function test_parse_return_duplicated() {
        $comment = '
            /**
             * @Select
             *
             * foo, bar, baz
             *
             * @return Entity[]
             * @return string[]
             */
        ';
        
        $uses = [
            'Omelet\Tests\Target',
            'Omelet\Annotation',
            __NAMESPACE__,
            
        ];
        $factory = new AnnotationConverter($uses);
        try {
            $factory->parse($comment, '');
            
            $this->fail();
        }
        catch (\Exception $ex) {
            if ($ex instanceof PHPUnit_Framework_AssertionFailedError) {
                throw $ex;
            }
            
            $this->assertInstanceOf(AnnotationException::class, $ex);
        }
     }
     
    /**
     * @test
     */
     public function test_parse_return_invalid_class() {
        $comment = '
            /**
             * @Select
             *
             * foo, bar, baz
             *
             * @return Hoge
             */
        ';
        
        $uses = [
            'Omelet\Tests\Target',
            'Omelet\Annotation',
            __NAMESPACE__,
            
        ];
        $factory = new AnnotationConverter($uses);
        
        try {
            $factory->parse($comment, '');
            
            $this->fail();
        }
        catch (\Exception $ex) {
            if ($ex instanceof PHPUnit_Framework_AssertionFailedError) {
                throw $ex;
            }
            
            $this->assertInstanceOf(AnnotationException::class, $ex);
        }
     }
     
    /**
     * @test
     */
     public function test_parse_param_primitive() {
        $comment = "
            /**
             * @Select
             *
             * foo, bar, baz
             *
             * @param string bar
             * @param integer baz
             */
        ";
        
        $uses = [
            'Omelet\Tests\Target',
            'Omelet\Annotation',
            __NAMESPACE__,
            
        ];
        $factory = new AnnotationConverter($uses);
        $annotations = $factory->parse($comment, '');
        
        $this->assertCount(2, $annotations['params']);
        $this->assertInstanceOf(ParamAlt::class, $annotations['params'][0]);
        $this->assertEquals('string', $annotations['params'][0]->type);
        $this->assertEquals('bar', $annotations['params'][0]->name);
        $this->assertInstanceOf(ParamAlt::class, $annotations['params'][0]);
        $this->assertEquals('integer', $annotations['params'][1]->type);
        $this->assertEquals('baz', $annotations['params'][1]->name);

        $this->assertCount(0, $annotations['returns']);
     }
     
    /**
     * @test
     */
     public function test_parse_param_primitive_array() {
        $comment = "
            /**
             * @Select
             *
             * foo, bar, baz
             *
             * @param string[] bar
             */
        ";
        
        $uses = [
            'Omelet\Tests\Target',
            'Omelet\Annotation',
            __NAMESPACE__,
            
        ];
        $factory = new AnnotationConverter($uses);
        $annotations = $factory->parse($comment, '');
        
        $this->assertCount(1, $annotations['params']);
        $this->assertInstanceOf(ParamAlt::class, $annotations['params'][0]);
        $this->assertEquals('string[]', $annotations['params'][0]->type);

        $this->assertCount(0, $annotations['returns']);
     }
     
    /**
     * @test
     */
     public function test_parse_param_builtin_class() {
        $comment = "
            /**
             * @Select
             *
             * foo, bar, baz
             *
             * @param DateTime bar
             * @param integer baz
             */
        ";
        
        $uses = [
            'Omelet\Tests\Target',
            'Omelet\Annotation',
            __NAMESPACE__,
            
        ];
        $factory = new AnnotationConverter($uses);
        $annotations = $factory->parse($comment, '');
        
        $this->assertCount(2, $annotations['params']);
        $this->assertInstanceOf(ParamAlt::class, $annotations['params'][0]);
        $this->assertEquals(\DateTime::class, $annotations['params'][0]->type);
        $this->assertEquals('bar', $annotations['params'][0]->name);
        $this->assertInstanceOf(ParamAlt::class, $annotations['params'][0]);
        $this->assertEquals('integer', $annotations['params'][1]->type);
        $this->assertEquals('baz', $annotations['params'][1]->name);

        $this->assertCount(0, $annotations['returns']);
     }
     
    /**
     * @test
     */
     public function test_parse_param_custom_class() {
        $comment = "
            /**
             * @Select
             *
             * foo, bar, baz
             *
             * @param DateTime bar
             * @param Select foo
             */
        ";
        
        $uses = [
            'Omelet\Tests\Target',
            'Omelet\Annotation',
            __NAMESPACE__,
            
        ];
        $factory = new AnnotationConverter($uses);
        $annotations = $factory->parse($comment, '');
        
        $this->assertCount(2, $annotations['params']);
        $this->assertInstanceOf(ParamAlt::class, $annotations['params'][0]);
        $this->assertEquals(\DateTime::class, $annotations['params'][0]->type);
        $this->assertEquals('bar', $annotations['params'][0]->name);
        $this->assertInstanceOf(ParamAlt::class, $annotations['params'][0]);
        $this->assertEquals(Select::class, $annotations['params'][1]->type);
        $this->assertEquals('foo', $annotations['params'][1]->name);

        $this->assertCount(0, $annotations['returns']);
     }
     
    /**
     * @test
     */
     public function test_parse_both() {
        $comment = "
            /**
             * @Select
             *
             * foo, bar, baz
             *
             * @param DateTime foo
             * @return int
             */
        ";
        
        $uses = [
            'Omelet\Tests\Target',
            'Omelet\Annotation',
            __NAMESPACE__,
            
        ];
        $factory = new AnnotationConverter($uses);
        $annotations = $factory->parse($comment, '');
        
        $this->assertCount(1, $annotations['params']);
        $this->assertInstanceOf(ParamAlt::class, $annotations['params'][0]);
        $this->assertEquals(\DateTime::class, $annotations['params'][0]->type);
        $this->assertEquals('foo', $annotations['params'][0]->name);

        $this->assertCount(1, $annotations['returns']);
        $this->assertInstanceOf(Returning::class, $annotations['returns'][0]);
        $this->assertEquals('int', $annotations['returns'][0]->type);
     }
     
    /**
     * @test
     */
     public function test_parse_var_primitive() {
        $comment = "
            /**
             * foo, bar, baz
             *
             * @var bool
             */
        ";
        
        $uses = [
            'Omelet\Tests\Target',
            'Omelet\Annotation',
            __NAMESPACE__,
            
        ];
        $factory = new AnnotationConverter($uses);
        $annotations = $factory->parse($comment, '');
        
        $this->assertCount(1, $annotations['vars']);
        $this->assertInstanceOf(ColumnType::class, $annotations['vars'][0]);
        $this->assertEquals('bool', $annotations['vars'][0]->type);

        $this->assertCount(0, $annotations['params']);
        $this->assertCount(0, $annotations['returns']);
     }
     
    /**
     * @test
     */
     public function test_parse_var_primitive_array() {
        $comment = "
            /**
             * foo, bar, baz
             *
             * @var float[]
             */
        ";
        
        $uses = [
            'Omelet\Tests\Target',
            'Omelet\Annotation',
            __NAMESPACE__,
            
        ];
        $factory = new AnnotationConverter($uses);
        $annotations = $factory->parse($comment, '');
        
        $this->assertCount(1, $annotations['vars']);
        $this->assertInstanceOf(ColumnType::class, $annotations['vars'][0]);
        $this->assertEquals('float[]', $annotations['vars'][0]->type);

        $this->assertCount(0, $annotations['params']);
        $this->assertCount(0, $annotations['returns']);
     }
     
    /**
     * @test
     */
     public function test_parse_var_builtin_class() {
        $comment = "
            /**
             * foo, bar, baz
             *
             * @var DateInterval
             */
        ";
        
        $uses = [
            'Omelet\Tests\Target',
            'Omelet\Annotation',
            __NAMESPACE__,
            
        ];
        $factory = new AnnotationConverter($uses);
        $annotations = $factory->parse($comment, '');
        
        $this->assertCount(1, $annotations['vars']);
        $this->assertInstanceOf(ColumnType::class, $annotations['vars'][0]);
        $this->assertEquals(\DateInterval::class, $annotations['vars'][0]->type);

        $this->assertCount(0, $annotations['params']);
        $this->assertCount(0, $annotations['returns']);
     }
     
    /**
     * @test
     */
     public function test_parse_var_custom_class_array() {
        $comment = "
            /**
             * foo, bar, baz
             *
             * @var Update[]
             */
        ";
        
        $uses = [
            'Omelet\Tests\Target',
            'Omelet\Annotation',
            __NAMESPACE__,
            
        ];
        $factory = new AnnotationConverter($uses);
        $annotations = $factory->parse($comment, '');
        
        $this->assertCount(1, $annotations['vars']);
        $this->assertInstanceOf(ColumnType::class, $annotations['vars'][0]);
        $this->assertEquals(Update::class . '[]', $annotations['vars'][0]->type);

        $this->assertCount(0, $annotations['params']);
        $this->assertCount(0, $annotations['returns']);
     }
     
    /**
     * @test
     */
     public function test_parse_var_duplicated() {
        $comment = "
            /**
             * foo, bar, baz
             *
             * @var Update[]
             * @var bool
             */
        ";
        
        $uses = [
            'Omelet\Tests\Target',
            'Omelet\Annotation',
            __NAMESPACE__,
            
        ];

        $factory = new AnnotationConverter($uses);
        try {
            $factory->parse($comment, '');
            
            $this->fail();
        }
        catch (\Exception $ex) {
            if ($ex instanceof PHPUnit_Framework_AssertionFailedError) {
                throw $ex;
            }
            
            $this->assertInstanceOf(AnnotationException::class, $ex);
        }
    }
}
