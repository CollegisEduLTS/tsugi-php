<?php

use \Tsugi\Util\U;

class UTest extends \PHPUnit\Framework\TestCase
{
    public function testGet() {
        $this->assertFalse(U::goodFolder(' '));
        $this->assertFalse(U::goodFolder('a b'));
        $this->assertTrue(U::goodFolder('ab'));
        $this->assertFalse(U::goodFolder('1a'));
        $this->assertFalse(U::goodFolder('ai!'));
        $this->assertFalse(U::goodFolder('ASJHJGAai!'));
        $this->assertTrue(U::goodFolder('ASJHJGAai'));
        $this->assertTrue(U::goodFolder('ASJ-JGAai'));
        $this->assertTrue(U::goodFolder('ASJ_JGAai'));
        $this->assertFalse(U::goodFolder('-ASJ_JGAai'));
        $this->assertFalse(U::goodFolder('_ASJ_JGAai'));
    }

    public function testRest() {
        // Note this is normally $_SERVER['REQUEST_URI'] so there is not http:// ...
        $this->assertEquals(U::get_rest_path('/py4e/lessons/intro'), '/py4e/lessons/intro');
        $this->assertEquals(U::get_rest_path('/py4e/lessons/intro/'), '/py4e/lessons/intro');
        $this->assertEquals(U::get_rest_path('/py4e/lessons/intro?x=2'), '/py4e/lessons/intro');
        $this->assertEquals(U::get_rest_path('/py4e/lessons/intro/?x=2'), '/py4e/lessons/intro');

        $this->assertEquals(U::get_rest_parent('/py4e/lessons/intro'), '/py4e/lessons');
        $this->assertEquals(U::get_rest_parent('/py4e/lessons/intro?x=2'), '/py4e/lessons');
        $this->assertEquals(U::get_rest_parent('/py4e/lessons/intro/'), '/py4e/lessons/intro');
        $this->assertEquals(U::get_rest_parent('/py4e/lessons/intro/?x=2'), '/py4e/lessons/intro');
    }

    public function testRelative() {
        $this->assertEquals(U::remove_relative_path('/a/b/c'), '/a/b/c');
        $this->assertEquals(U::remove_relative_path('/a/b/c/'), '/a/b/c/');
        $this->assertEquals(U::remove_relative_path('/a/./c/'), '/a/c/');
        $this->assertEquals(U::remove_relative_path('/a/../c/'), '/c/');
        $this->assertEquals(U::remove_relative_path('/a/b/../../c/'), '/c/');
    }

    public function testParseController() {
        global $CFG;
        if ( ! isset($CFG) ) $CFG = new \stdclass();
        $this->assertEquals(U::parse_rest_path('/a/b/c','/a/koseu.php'), array('/a','b','c'));
        $this->assertEquals(U::parse_rest_path('/py4e/lessons/intro?x=2','/py4e/koseu.php'), array('/py4e','lessons','intro'));
        $this->assertEquals(U::parse_rest_path('/py4e/lessons/intro/fred/sarah?x=2','/py4e/koseu.php'), array('/py4e','lessons','intro/fred/sarah'));
        $this->assertEquals(U::parse_rest_path('/lessons/intro?x=2','/koseu.php'), array('','lessons','intro'));
        $this->assertEquals(U::parse_rest_path('/lessons/intro/fred/sarah?x=2','/koseu.php'), array('','lessons','intro/fred/sarah'));

        // Running from the wrong directory
        $this->assertEquals(U::parse_rest_path('/a/b/c','/x/koseu.php'), false);

        // Object version...
        $CFG->wwwroot = "http://www.example.com:8888/tsugi";
        $path = U::rest_path('/py4e/lessons/intro?x=2','/py4e/koseu.php');
        $this->assertEquals($path->base_url,'http://www.example.com:8888');
        $this->assertEquals($path->parent,'/py4e');
        $this->assertEquals($path->controller,'lessons');
        $this->assertEquals($path->extra,'intro');
        $path = U::rest_path('/py4e/lessons/intro/fred/sarah?x=2','/py4e/koseu.php');
        $this->assertEquals($path->base_url,'http://www.example.com:8888');
        $this->assertEquals($path->parent,'/py4e');
        $this->assertEquals($path->controller,'lessons');
        $this->assertEquals($path->extra,'intro/fred/sarah');
        $path = U::rest_path('/lessons/intro?x=2','/koseu.php');
        $this->assertEquals($path->base_url,'http://www.example.com:8888');
        $this->assertEquals($path->parent,'');
        $this->assertEquals($path->controller,'lessons');
        $this->assertEquals($path->extra,'intro');
        $path = U::rest_path('/lessons/intro/fred/sarah?x=2','/koseu.php');
        $this->assertEquals($path->base_url,'http://www.example.com:8888');
        $this->assertEquals($path->parent,'');
        $this->assertEquals($path->controller,'lessons');
        $this->assertEquals($path->extra,'intro/fred/sarah');
        $this->assertEquals($path->action,'intro');
        $this->assertEquals($path->parameters,array('fred', 'sarah'));

        // When there is no controller and we fall into index.php directly
        $path = U::rest_path('/a','/a/index.php');
        $this->assertEquals($path->base_url,'http://www.example.com:8888');
        $this->assertEquals($path->parent,'/a');
        $this->assertEquals($path->controller,'');
        $this->assertEquals($path->extra,'');
        $this->assertEquals($path->full,'/a');
        $this->assertEquals($path->current,'/a');
        $this->assertEquals($path->action,'');
        $this->assertEquals($path->parameters,array());

        // When there is no controller and we go to index.php
        $path = U::rest_path('/a/','/a/index.php');
        $this->assertEquals($path->base_url,'http://www.example.com:8888');
        $this->assertEquals($path->parent,'/a');
        $this->assertEquals($path->controller,'');
        $this->assertEquals($path->extra,'');
        $this->assertEquals($path->full,'/a');
        $this->assertEquals($path->current,'/a');
        $this->assertEquals($path->action,'');
        $this->assertEquals($path->parameters,array());
    }

    // https://stackoverflow.com/questions/30231476/i-want-to-array-key-and-array-value-comma-separated-string
    // https://stackoverflow.com/questions/4923951/php-split-string-in-key-value-pairs
    public function testSerialization() {
        $arar = Array ( 1 => 42 ,2 => 43, 3 => 44 );
        $str = U::array_Integer_Serialize($arar);
        $this->assertEquals($str, '1=42,2=43,3=44');
        $newa = U::array_Integer_Deserialize($str);
        $this->assertEquals($arar, $newa);
    }

    // http://php.net/manual/en/function.array-shift.php#84179
    public function testKShift() {
        $arr = array('x'=>'ball','y'=>'hat','z'=>'apple');
        $thing = U::array_kshift($arr);
        $this->assertEquals($thing,array('x'=>'ball'));
        $this->assertEquals($arr,array('y'=>'hat','z'=>'apple'));
    }

    public function testgetServerBase() {
        $a = array(
            'https://www.py4e.com' => 'https://www.py4e.com',
            'https://www.py4e.com/abc' => 'https://www.py4e.com',
            'https://www.py4e.com:443/abc' => 'https://www.py4e.com:443',
            'http://localhost:8888/abc' => 'http://localhost:8888',
            'http://localhost:8888' => 'http://localhost:8888',
        );

        foreach($a as $k => $v ) {
            $retval = U::getServerBase($k);
            $this->assertEquals($retval, $v);
        }
    }

    public function testhtmlspec_utf8() {
        $t1 = U::htmlspec_utf8("zap");
        $this->assertEquals($t1, "zap");
        $t1 = U::htmlspec_utf8(42);
        $this->assertEquals($t1, 42);
        $t1 = U::htmlspec_utf8("zap'zap");
        $this->assertEquals($t1, "zap&#039;zap");
        $t1 = U::htmlspec_utf8("zap&zap");
        $this->assertEquals($t1, "zap&amp;zap");
        $t1 = U::htmlspec_utf8("zap?zap");
        $this->assertEquals($t1, "zap?zap");
        $t1 = U::htmlspec_utf8("zap<zap");
        $this->assertEquals($t1, "zap&lt;zap");
        // PHP 8.1
        $t1 = U::htmlspec_utf8(null);
        $this->assertEquals($t1, "");
        // Arrary is just a guardian test
        $t1 = U::htmlspec_utf8(array('bob'));
        $this->assertEquals($t1, array('bob'));
    }


    public function testhtmlent_utf8() {
        $t1 = U::htmlent_utf8("zap");
        $this->assertEquals($t1, "zap");
        $t1 = U::htmlent_utf8(42);
        $this->assertEquals($t1, 42);
        $t1 = U::htmlent_utf8("zap'zap");
        $this->assertEquals($t1, "zap&#039;zap");
        $t1 = U::htmlent_utf8("zap&zap");
        $this->assertEquals($t1, "zap&amp;zap");
        $t1 = U::htmlent_utf8("zap?zap");
        $this->assertEquals($t1, "zap?zap");
        $t1 = U::htmlent_utf8("zap<zap");
        $this->assertEquals($t1, "zap&lt;zap");
        // PHP 8.1
        $t1 = U::htmlent_utf8(null);
        $this->assertEquals($t1, "");
    }
}
