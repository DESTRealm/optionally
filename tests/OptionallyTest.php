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
     * Test Optionally's instantiation.
     * @return [type]
     */
    public function testInstantiation ()
    {
        // Instantiation without passed in arguments, using only $_SERVER['argv'].
        $_SERVER['argv'] = array('--debug', '-c', 'file.config');
        $optionally = new Optionally();
        $options = $optionally
            ->option('debug')
                ->describe('Enable debugging.')
                ->boolean()
            ->option('c')
                ->describe('Loads a configuration file.')
                ->value()
            ->argv()
            ;

        $this->assertEquals('file.config', $options->c);
        $this->assertTrue($options->debug);

        // Instantiation passing in arguments.
        $optionally = new Optionally(array('--debug', '-c', 'file.config'));
        $options = $optionally
            ->option('debug')
                ->describe('Enable debugging.')
                ->boolean()
            ->option('c')
                ->describe('Loads a configuration file.')
                ->value()
            ->argv()
            ;

        $this->assertEquals('file.config', $options->c);
        $this->assertTrue($options->debug);
    } // end testInstantiation ()

    /**
     * Test initializaing Optionally using its factory method
     * Optionally::options(). This is the recommended method since it allows for
     * slightly more compact code.
     * @return [type]
     */
    public function testFactoryMethod ()
    {
        // Factory method using $_SERVER['argv'].
        $_SERVER['argv'] = array('--debug', '-c', 'file.config');
        $options = Optionally::options()
            ->option('debug')
                ->describe('Enable debugging.')
                ->boolean()
            ->option('c')
                ->describe('Loads a configuration file.')
                ->value()
            ->argv()
            ;

        $this->assertEquals('file.config', $options->c);
        $this->assertTrue($options->debug);

        // Factory method providing an arguments list.
        $options = Optionally::options(array('--debug', '-c', 'file.config'))
            ->option('debug')
                ->describe('Enable debugging.')
                ->boolean()
            ->option('c')
                ->describe('Loads a configuration file.')
                ->value()
            ->argv()
            ;

        $this->assertEquals('file.config', $options->c);
        $this->assertTrue($options->debug);
    } // end testFactoryMethod ()

    /**
     * Test basic option creation.
     * @return [type]
     */
    public function testCreateOption ()
    {
        $options = Optionally::options(array('-c', 'file', '--file', 'test.txt', 'arg1'))
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
        $options = Optionally::options(array('--debug', '-c', 'file', 'arg0'))
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
        $options = Optionally::options(array('--source=config.txt', '-c', 'file'))
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
     * @expectedException org\destrealm\utilities\optionally\OptionallyGetoptException
     */
    public function testRequiredValuesShortOptFailure ()
    {
        $options = Optionally::options(array('--source=config.txt', '-c'))
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
     * @expectedException org\destrealm\utilities\optionally\OptionallyGetoptException
     */
    public function testRequiredValuesLongOptFailure ()
    {
        $options = Optionally::options(array('--source', '-c', 'file'))
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
        $options = Optionally::options(array('--debug', '-c', 'file'))
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

        $options = Optionally::options(array('--debug=file', '-c'))
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

    /**
     * Tests arguments passed in addition to options.
     * @return [type]
     */
    public function testArguments ()
    {
        $options = Optionally::options(array('--debug', '-c', 'file', '/home', '/usr/bin'))
            ->option('debug')
                ->describe('Enables debugging with an optional level between 1 and 9.')
                ->value()
                    ->optional()
            ->option('c')
                ->describe('Loads a configuration file.')
                ->value()
            ->argv()
            ;

        $args = $options->args();
        $this->assertEquals('/home', $args[0]);
        $this->assertEquals('/usr/bin', $args[1]);

        $this->assertEquals('/home', $options->args(0));
        $this->assertEquals('/usr/bin', $options->args(1));

        $this->assertNull($options->args(2));
    } // end testArguments ()

    public function testAliases ()
    {

        $options = Optionally::options(array(
            '--debug', '-o', '-v'
        ))
            ->option('debug')
                ->alias('d')
                ->describe('Enable debugging output.')
                ->boolean()
            ->option('o')
                ->alias('std-out')
                ->describe('Output processed data via STDOUT.')
                ->boolean()
            ->option('v')
                ->alias('verbose')
                ->alias('verbose-output')
                ->describe('Enable verbose output.')
                ->boolean()
            ;

        $this->assertTrue($options->d);
        $this->assertTrue($options->debug);

        $this->assertTrue($options->o);
        $this->assertTrue($options->std_out);
        $this->assertTrue($options->stdOut);

        $this->assertTrue($options->v);
        $this->assertTrue($options->verbose);
        $this->assertTrue($options->verbose_output);
        $this->assertTrue($options->verboseOutput);

    } // end testAliases ()

} // end OptionallyTest
