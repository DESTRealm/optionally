<?php

namespace DESTRealm\Optionally\Tests;

use DESTRealm\Optionally;

/**
 * Optionally unit tests for README validation.
 *
 * This unit test case includes all source examples currently lists in the
 * README for validation. Be aware that due to the nature of projects and how
 * asynchronous changes can crop up, this test case may lag behind the README
 * by a few commits.
 */
class ReadmeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Basic Usage
     */
    public function testBasicUsage ()
    {

        $_SERVER['argv'] = array('script.php', '--test=1', '-v', '--debug', 'file.txt', 'output.txt');
        $options = Optionally::options()
            ->argv()
            ;

        $this->assertEquals('file.txt', $options->args(0));
        $this->assertEquals('output.txt', $options->args(1));

        $args = $options->args();

        $this->assertEquals('file.txt', $args[0]);
        $this->assertEquals('output.txt', $args[1]);

    } // end testBaseUsage ()

    /**
     * Optionally and Options
     */
    public function testOptionallyAndOptions ()
    {

        $_SERVER['argv'] = array('script.php', '--test=1', '-v', '--debug', 'file.txt', 'output.txt');
        $options = Optionally::options()
          ->option('test')
            ->value()
          ->option('v')
            ->boolean()
          ->option('debug')
            ->boolean()
          ->argv()
          ;
        $this->assertEquals('1', $options->test);
        $this->assertTrue($options->v);
        $this->assertTrue($options->debug);

        $_SERVER['argv'] = array('script.php', '--test=1', '-v', 'file.txt', 'output.txt');
        $options = Optionally::options()
          ->option('test')
            ->value()
          ->option('v')
            ->boolean()
          ->option('debug')
            ->boolean()
          ->argv()
          ;
        $this->assertEquals('1', $options->test);
        $this->assertTrue($options->v);
        $this->assertFalse($options->debug);

        $_SERVER['argv'] = array('script.php', '-v', 'file.txt', 'output.txt');
        $options = Optionally::options()
          ->option('test')
            ->value()
          ->option('v')
            ->boolean()
          ->option('debug')
            ->boolean()
          ->argv()
          ;
        $this->assertNull($options->test);

    } // end testOptionallyAndOptions ()

    /**
     * Options Object
     */
    public function testOptionsObject ()
    {

        $_SERVER['argv'] = array('script.php', '--without-foo');
        $options = Optionally::options()
          ->option('with-bar')
            ->boolean()
          ->option('without-foo')
            ->boolean()
          ->argv()
          ;
        $this->assertTrue($options->withoutFoo);
        $this->assertTrue($options->without_foo);
        $this->assertFalse($options->withBar);
        $this->assertFalse($options->with_bar);

    } // end testOptionsObject ()

    /**
     * Advanced Options: Aliases!
     */
    public function testAdvancedOptionsAliases ()
    {

        $_SERVER['argv'] = array('script.php', '-v');
        $options = Optionally::options()
          ->option('v')
            ->boolean()
            ->alias('verbose')
          ->argv()
          ;
        $this->assertTrue($options->v);
        $this->assertTrue($options->verbose);

        $_SERVER['argv'] = array('script.php', '--debug');
        $options = Optionally::options()
          ->option('debug')
            ->alias('d')
            ->boolean()
          ->option('v')
            ->boolean()
            ->alias('verbose')
          ->argv()
          ;
        $this->assertTrue($options->debug);
        $this->assertTrue($options->d);
        $this->assertFalse($options->v);
        $this->assertFalse($options->verbose);
    } // end testAdvancedOptionsAliases ()

    /**
     * Advanced Options: Optional Values!
     */
    public function testAdvancedOptionsOptionalValues ()
    {

        $_SERVER['argv'] = array('script.php', '-v', '--number', '--count=5');
        $options = Optionally::options()
          ->option('v')
            ->alias('verbose')
            ->boolean()
          ->option('number')
            ->alias('n')
            ->value(0)
          ->option('count')
            ->value(0)
          ->option('max')
            ->value(0)
          ->argv()
          ;
        $this->assertTrue($options->v);
        $this->assertTrue($options->verbose);
        $this->assertEquals(0, $options->number);
        $this->assertEquals(0, $options->n);
        $this->assertEquals(5, $options->count);
        $this->assertEquals(0, $options->max);

        $_SERVER['argv'] = array('script.php', '--count');
        $options = Optionally::options()
          ->option('count')
            ->alias('c')
            ->value('')
          ->argv()
          ;
        $this->assertEquals('', $options->count);

        $_SERVER['argv'] = array('script.php');
        $options = Optionally::options()
          ->option('count')
            ->alias('c')
            ->value('')
          ->argv()
          ;
        $this->assertEquals('', $options->count);

        $_SERVER['argv'] = array('script.php', '--count=15');
        $options = Optionally::options()
          ->option('count')
            ->alias('c')
            ->value('')
          ->argv()
          ;
        $this->assertEquals(15, $options->count);
    } // end testAdvancedOptionsOptionalValues ()

    /**
     * Really Advanced Options: Mostly Optional Values with Difference Defaults!
     */
    public function testAdvancedOptionsMovwdd ()
    {
        $_SERVER['argv'] = array('script.php');
        $options = Optionally::options()
          ->option('count')
            ->value()
              ->optional()
              ->defaults(0)
              ->defaultsIfMissing(false)
          ->argv()
          ;
        $this->assertFalse($options->count);

        $_SERVER['argv'] = array('script.php', '--count');
        $options = Optionally::options()
          ->option('count')
            ->value()
              ->optional()
              ->defaults(0)
              ->defaultsIfMissing(false)
          ->argv()
          ;
        $this->assertEquals(0, $options->count);

        $_SERVER['argv'] = array('script.php', '--count', '15');
        $options = Optionally::options()
          ->option('count')
            ->value()
              ->optional()
              ->defaults(0)
              ->defaultsIfMissing(false)
          ->argv()
          ;
        $this->assertEquals(15, $options->count);
    } // end testAdvancedOptionsMovwdd ()

    /**
     * Really Advanced Options: Countable Options as Array Options!
     */
    public function testAdvancedOptionsCountableArrays ()
    {
        $_SERVER['argv'] = array('script.php', '-v', '-v', 'file.txt');
        $options = Optionally::options()
          ->option('verbose')
            ->alias('v')
            ->isCountable() // countable() is an alias to this.
          ->argv()
          ;
        $this->assertEquals('2', $options->verbose);
        $this->assertEquals('2', $options->v);

        $_SERVER['argv'] = array('script.php', '-v', '-v', '-v', '-v');
        $options = Optionally::options()
          ->option('verbose')
            ->alias('v')
            ->isCountable() // countable() is an alias to this.
          ->argv()
          ;
        $this->assertEquals('4', $options->verbose);
        $this->assertEquals('4', $options->v);

        $_SERVER['argv'] = array('script.php', '--filter', 'bw', '--filter', 'mosaic');
        $options = Optionally::options()
          ->option('filter')
            ->isArray()
          ->argv()
          ;
        $this->assertEquals(array('bw', 'mosaic'), $options->filter);
    } // end testAdvancedOptionsCountableArrays ()

    /**
     * Really Advanced Options: Test Option Values
     * @return [type] [description]
     */
    public function testAdvancedOptionsTestOptionValues ()
    {
        $triggered = false;
        $_SERVER['argv'] = array('script.php', '--number', '100');
        $options = Optionally::options()
            ->option('number')
                ->filter(function($value) use (&$triggered){
                    if (is_numeric($value)) {
                        $triggered = true;
                        return (int)$value;
                    }
                    return 0;
                })
            ->argv()
            ;
        $this->assertTrue($triggered);
        $this->assertEquals(100, $options->number);

        $triggered = false;
        $_SERVER['argv'] = array('script.php', '--number', 'blah');
        $options = Optionally::options()
            ->option('number')
                ->filter(function($value) use (&$triggered){
                    if (is_numeric($value)) {
                        return (int)$value;
                    }
                    $triggered = true;
                    return 0;
                })
            ->argv()
            ;
        $this->assertTrue($triggered);
        $this->assertEquals(0, $options->number);

        $_SERVER['argv'] = array('script.php', '--number', '100');
        $options = Optionally::options()
            ->option('number')
                ->value(0)
                ->test(function($value){
                    return (bool)preg_match('#[0-9]+#', $value) !== false;
                })
            ->argv()
            ;
        $this->assertEquals('100', $options->number);

        $_SERVER['argv'] = array('script.php', '--number', '100');
        $options = Optionally::options()
            ->option('number')
                ->value(0)
                ->test(function($value){
                    return (bool)preg_match('#[0-9]+#', $value) !== false;
                })
            ->argv()
            ;
        $this->assertEquals('100', $options->number);

        $_SERVER['argv'] = array('script.php', '--number', 'blah');
        $options = Optionally::options()
            ->option('number')
                ->value(0) // The 0 here ensures that an exception will not be
                           // thrown.
                ->test(function($value){
                    return (bool)preg_match('#[0-9]+#', $value) !== false;
                })
            ->argv()
            ;
        $this->assertEquals('0', $options->number);

        $_SERVER['argv'] = array('script.php', '--number', 'blah');
        $options = Optionally::options()
            ->option('number')
                ->value(0) // The 0 here ensures that an exception will not be
                           // thrown.
                ->test(function($value){
                    return (bool)preg_match('#[0-9]+#', $value) !== false;
                },
                0)
            ->argv()
            ;
        $this->assertEquals('0', $options->number);

        $_SERVER['argv'] = array('script.php', '--filter', 'imageExpand', '--filter', 'imageScale', '--filter', 'undefined');
        $options = Optionally::options()
            ->option('filter')
                ->isArray()
                ->test(function($value){return in_array($value, array('imageExpand', 'imageScale'));}, 'scale')
            ->argv()
            ;
        $this->assertEquals(array('imageExpand', 'imageScale', 'scale'), $options->filter);

        $_SERVER['argv'] = array('script.php', '--filter', '1', '--filter', '100', '--filter', 'foobar');
        $options = Optionally::options()
            ->option('filter')
                ->isArray()
                ->filter(function($value){return (int)$value;})
            ->argv()
            ;
        $this->assertEquals(array(1, 100, 0), $options->filter);
    } // end testAdvancedOptionsTestOptionValues ()

    /**
     * Really Advanced Options: Test Option Values (exception handling)
     * @expectedException DESTRealm\Optionally\Exceptions\OptionsValueException
     */
    public function testAdvancedOptionsTestOptionValues2 ()
    {
        $_SERVER['argv'] = array('script.php', '--number', 'blah');
        $options = Optionally::options()
            ->option('number')
                ->value()
                ->test(function($value){
                    return (bool)preg_match('#[0-9]+#', $value) !== false;
                })
            ->argv()
            ;
    } // end testAdvancedOptionsTestOptionValues2 ()

    /**
     * Advanced (but stupid) Options: Required Options!
     */
    public function testAdvancedStupidity ()
    {
        $_SERVER['argv'] = array('script.php', '--require-me');
        $options = Optionally::options()
            ->option('require-me')
                ->alias('r')
                ->value(100)
                ->required()    // don't do this
            ->argv()
            ;
        $this->assertEquals(100, $options->requireMe);
        $this->assertEquals(100, $options->require_me);
        $this->assertEquals(100, $options->r);

        $_SERVER['argv'] = array('script.php', '-r');
        $options = Optionally::options()
            ->option('require-me')
                ->alias('r')
                ->value(100)
                ->required()    // don't do this
            ->argv()
            ;
        $this->assertEquals(100, $options->requireMe);
        $this->assertEquals(100, $options->require_me);
        $this->assertEquals(100, $options->r);
    } // end testAdvancedStupidity ()

    /**
     * Advanced (but stupid) Options: Required Options!
     * @expectedException DESTRealm\Optionally\Exceptions\OptionsException
     */
    public function testAdvancedStupidity2 ()
    {
        $_SERVER['argv'] = array('script.php');
        $options = Optionally::options()
            ->option('require-me')
                ->alias('r')
                ->value(100)
                ->required()    // don't do this
            ->argv()
            ;
    } // end testAdvancedStupidity2 ()


} // end ReadmeTest