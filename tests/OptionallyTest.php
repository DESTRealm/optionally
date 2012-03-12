<?php

namespace org\destrealm\utilities;

use PHPUnit_Framework_TestCase;

require_once 'optionally.php';

class OptionallyTest extends PHPUnit_Framework_TestCase
{

    public function testCreateOption ()
    {
        $options = new Optionally(array('-c', 'file', '--file', 'test.txt', 'arg1'));
        $options
            ->option('c')
                ->required()
                ->describe('Loads a config file.')
                ->alias('config')
                ->value()
            ->option('file')
                ->describe('Dumps output into the specified file, if provided, '.
                          'or STDOUT if not.')
                ->alias('f')
                ->value()
                    ->optional()
            ;

        $this->assertEquals('file', $options->c);
        $this->assertNull($options->file);

        $args = $options->args();

        $this->assertEquals('test.txt', $args[0]);
        $this->assertEquals('arg1', $args[1]);

    } // end testCreateOption ()

    public function testBoolean ()
    {
        $options = new Optionally(array('--debug', '-c', 'file', 'arg0'));
        $options
            ->option('debug')
                ->boolean()
                ->describe('Enables debugging mode.')
            ->option('disable-fleece')
                ->boolean()
                ->describe('Disables the fleece on our sheep.')
            ->option('c')
                ->alias('config')
                ->describe('Specify a configuration file.')
                ->value()
            ;

        $args = $options->args();

        $this->assertTrue($options->debug);
        $this->assertFalse($options->disable_fleece);
        $this->assertEquals('file', $options->c);
        $this->assertEquals('arg0', $args[0]);
    } // end testBoolean ()

    public function testOptionalValues ()
    {
        $options = new Optionally(array('--debug', '-c', 'file'));
        $options
            ->option('debug')
                ->describe('Enables debugging mode.')
                ->value()
                    ->optional()
            ->option('c')
                ->describe('Specify a configuration file.')
                ->value()
                    ->optional()
            ;

        $this->assertNull($options->debug);
        $this->assertEquals('file', $options->c);
    } // end testOptionalValues ()

} // end OptionallyTest