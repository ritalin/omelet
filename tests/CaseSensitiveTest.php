<?php

namespace OmeletTest;

use Omelet\Util\CaseSensor;

class CaseSensitiveTest extends \PHPUnit_Framework_TestCase {
    /**
     * @test
     */
    public function test_convert_from_sname() {
        fromLowerSnake: {
            $cs = CaseSensor::LowerSnake();
            $this->assertEquals("foo_bar_baz", $cs->convert("foo_bar_baz"));
            
            $cs = CaseSensor::UpperSnake();
            $this->assertEquals("FOO_BAR_BAZ", $cs->convert("foo_bar_baz"));
            
            $cs = CaseSensor::LowerCamel();
            $this->assertEquals("fooBarBaz", $cs->convert("foo_bar_baz"));
            
            $cs = CaseSensor::UpperCamel();
            $this->assertEquals("FooBarBaz", $cs->convert("foo_bar_baz"));
        }
        fromUpperSnake: {
            $cs = CaseSensor::LowerSnake();
            $this->assertEquals("foo_bar_baz", $cs->convert("FOO_BAR_BAZ"));
            
            $cs = CaseSensor::UpperSnake();
            $this->assertEquals("FOO_BAR_BAZ", $cs->convert("FOO_BAR_BAZ"));
            
            $cs = CaseSensor::LowerCamel();
            $this->assertEquals("fooBarBaz", $cs->convert("FOO_BAR_BAZ"));
            
            $cs = CaseSensor::UpperCamel();
            $this->assertEquals("FooBarBaz", $cs->convert("FOO_BAR_BAZ"));
        }
    }
    
    /**
     * @test
     */
    public function test_convert_from_camel() {
        fromLowerCamel: {
            $cs = CaseSensor::LowerSnake();
            $this->assertEquals("foo_bar_baz", $cs->convert("fooBarBaz"));
            
            $cs = CaseSensor::UpperSnake();
            $this->assertEquals("FOO_BAR_BAZ", $cs->convert("fooBarBaz"));
            
            $cs = CaseSensor::LowerCamel();
            $this->assertEquals("fooBarBaz", $cs->convert("fooBarBaz"));
            
            $cs = CaseSensor::UpperCamel();
            $this->assertEquals("FooBarBaz", $cs->convert("fooBarBaz"));
        }
        fromUpperCamel: {
            $cs = CaseSensor::LowerSnake();
            $this->assertEquals("foo_bar_baz", $cs->convert("FooBarBaz"));
            
            $cs = CaseSensor::UpperSnake();
            $this->assertEquals("FOO_BAR_BAZ", $cs->convert("FooBarBaz"));
            
            $cs = CaseSensor::LowerCamel();
            $this->assertEquals("fooBarBaz", $cs->convert("FooBarBaz"));
            
            $cs = CaseSensor::UpperCamel();
            $this->assertEquals("FooBarBaz", $cs->convert("FooBarBaz"));
        }
    }
}
