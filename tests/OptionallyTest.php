<?php

namespace org\destrealm\utilities\optionally;

use PHPUnit_Framework_TestCase;

require_once 'optionally.php';

/**
 * Optionally unit tests.
 * 
 * While these unit tests are fairly simple, they serve to demonstrate much of
 * the common use cases Optionally is intended to fulfill.
 */
class OptionallyTest extends PHPUnit_Framework_TestCase
{

    /**
     * Test basic option creation.
     * @return [type]
     */
    public function testCreateOption ()
    {
        $optionally = new Optionally(array('-c', 'file', '--file', 'test.txt', 'arg1'));
        $options = $optionally
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
            ->argv()
            ;

        $this->assertEquals('file', $options->c);
        $this->assertNull($options->file);

        $args = $options->args();

        $this->assertEquals('test.txt', $args[0]);
        $this->assertEquals('arg1', $args[1]);

    } // end testCreateOption ()

    /**
     * Test the creation of boolean options.
     * @return [type]
     */
    public function testBoolean ()
    {
        $optionally = new Optionally(array('--debug', '-c', 'file', 'arg0'));
        $options = $optionally
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
            ->argv()
            ;

        $args = $options->args();

        $this->assertTrue($options->debug);
        $this->assertFalse($options->disable_fleece);
        $this->assertEquals('file', $options->c);
        $this->assertEquals('arg0', $args[0]);
    } // end testBoolean ()

    /**
     * Test the creation of options that require values.
     */
    public function testRequiredValues ()
    {
        $optionally = new Optionally(array('--source=config.txt', '-c', 'file'));
        $options = $optionally
            ->option('source')
                ->describe('Configuration source.')
                ->value()
            ->option('c')
                ->describe('Configuration source type.')
                ->value()
            ->argv()
            ;

        $this->assertEquals('config.txt', $options->source);
        $this->assertEquals('file', $options->c);
    } // end testRequiredValues ()

    /**
     * Tests the creation of options that require values but have not been
     * supplied the appropriate values. Short option test.
     * @expectedException OptionallyGetoptException
     */
    public function testRequiredValuesShortOptFailure ()
    {
        $optionally = new Optionally(array('--source=config.txt', '-c'));
        $options = $optionally
            ->option('source')
                ->describe('Configuration source.')
                ->value()
            ->option('c')
                ->describe('Configuration source type.')
                ->value()
            ->argv()
            ;
    } // end testRequiredValuesShortOptFailure ()

    /**
     * Tests the creation of options that require values but have not been
     * supplied the appropriate values. Long option test.
     * @expectedException OptionallyGetoptException
     */
    public function testRequiredValuesLongOptFailure ()
    {
        $optionally = new Optionally(array('--source', '-c', 'file'));
        $options = $optionally
            ->option('source')
                ->describe('Configuration source.')
                ->value()
            ->option('c')
                ->describe('Configuration source type.')
                ->value()
            ->argv()
            ;
    } // end testRequiredValuesLongOptFailure ()

    /**
     * Test the creation of option value options (that is, options that may be
     * supplied with or without value arguments).
     */
    public function testOptionalValues ()
    {
        $optionally = new Optionally(array('--debug', '-c', 'file'));
        $options = $optionally
            ->option('debug')
                ->describe('Enables debugging mode.')
                ->value()
                    ->optional()
            ->option('c')
                ->describe('Specify a configuration file.')
                ->value()
                    ->optional()
            ->argv()
            ;

        $this->assertNull($options->debug);
        $this->assertEquals('file', $options->c);

        $optionally = new Optionally(array('--debug=file', '-c'));
        $options = $optionally
            ->option('debug')
                ->describe('Enables debugging mode.')
                ->value()
                    ->optional()
            ->option('c')
                ->describe('Specify a configuration file.')
                ->value()
                    ->optional()
            ->argv()
            ;

        $this->assertNull($options->c);
        $this->assertEquals('file', $options->debug);
    } // end testOptionalValues ()

} // end OptionallyTest