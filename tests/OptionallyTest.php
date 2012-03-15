<?php

namespace org\destrealm\utilities\optionally;

use PHPUnit_Framework_TestCase;

require_once 'optionally.php';

// Stop PHPUnit's test reports from complaining.
date_default_timezone_set('UTC');

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
        $_SERVER['argv'] = array('test.php', '--debug', '-c', 'file.config');
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
        $_SERVER['argv'] = array('test.php', '--debug', '-c', 'file.config');
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
     * This test tests a common circumstance where argv() may have been
     * forgotten.
     * @expectedException org\destrealm\utilities\optionally\OptionallyException
     * @expectedExceptionMessage Invalid option "debug." Did you forget to call argv()?
     * @covers org\destrealm\utilities\optionally\Optionally::__get
     */
    public function testForgottenArgvWarning ()
    {
        $options = Optionally::options(array('--debug'))
            ->option('debug')
                ->describe('Enable debugging.')
                ->boolean()
            ;

        $options->optionTemplate; // Override code coverage complains for __get.

        if ($options->debug) {
            // Nothing happens here.
        }
    } // end testForgottenArgvWarning ()

    /**
     * Test basic option creation.
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
     * Tests the creation of an option with its settings passed in as an array.
     * This is likely a lesser-used option since using method chaining is much
     * easier to read but might result in more compact code.
     */
    public function testCreateOptionWithSettingsArray ()
    {
        $options = Optionally::options(array('--debug', '-f', 'file.config'))
            ->option('debug', array('boolean' => true, 'aliases' => array('d')))
            ->option('f', array('value' => true, 'aliases' => array('file')))
            ->argv()
            ;

        $this->assertTrue($options->debug);
        $this->assertTrue($options->d);

        $this->assertEquals('file.config', $options->f);
        $this->assertEquals('file.config', $options->file);
    } // end testCreateOptionWithSettingsArray ()

    /**
     * Test the creation of boolean options.
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
     * Tests options required with Optionally::required().
     * @expectedException org\destrealm\utilities\optionally\OptionallyOptionsException
     * @expectedExceptionMessage Required option "f" was not provided!
     * @return [type]
     */
    public function testRequiredOptions ()
    {
        $options = Optionally::options(array())
            ->option('f')
                ->alias('file')
                ->required()
            ->argv()
            ;
    } // end testRequiredOptions ()

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

    /**
     * Tests aliasing assignments.
     */
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
            ->argv()
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

        // Test swapped arguments: Alias being called, master option ignored.
        $options = Optionally::options(array('--debug', '--file', 'file.config'))
            ->option('d')
                ->alias('debug')
                ->describe('Enable debugging mode.')
                ->boolean()
            ->option('f')
                ->alias('file')
                ->describe('Loads the file %file and prints it to STDOUT.')
                ->value()
            ->argv()
            ;

        $this->assertTrue($options->debug);
        $this->assertEquals('file.config', $options->file);

    } // end testAliases ()

    /**
     * @expectedException org\destrealm\utilities\optionally\OptionallyGetoptException
     * @expectedExceptionMessage Unrecognized option: c
     * @return [type]
     */
    public function testOptionsThatWerentConfigured ()
    {
        $options = Optionally::options(array('--debug', '-c', 'file.config'))
            ->option('debug')
                ->boolean()
            ->argv()
            ;

        $this->assertTrue($options->debug);
        $this->assertNull($options->c);
    } // end testOptionsThatWerentConfigured ()

    /**
     * Tests setting defaults.
     */
    public function testDefaults ()
    {

        // Default assignments with all options.
        $options = Optionally::options(array('--debug', '-v'))
            ->option('debug')
                ->boolean()
            ->option('v')
                ->alias('verbose')
                ->value()
                    ->optional()
                ->defaults(2)
            ->argv()
            ;

        $this->assertTrue($options->debug);
        $this->assertEquals(2, $options->v);
        $this->assertEquals(2, $options->verbose);

        // Default assignments with no options. "debug" should be false (it's
        // a boolean option) and "verbose" should be null as default values
        // are not honored when the value is missing.
        $options = Optionally::options(array())
            ->option('debug')
                ->boolean()
            ->option('v')
                ->alias('verbose')
                ->value()
                    ->optional()
                ->defaults(2)
            ->argv()
            ;

        $this->assertFalse($options->debug);
        $this->assertNull($options->v);
        $this->assertNull($options->verbose);

    } // end testDefaults ()

    /**
     * Tests assigning defaults with defaultsIfMissing().
     */
    public function testDefaultsIfMissing ()
    {
        // Default assignment with no options and defaultsIfMissing.
        $options = Optionally::options(array())
            ->option('debug')
                ->boolean()
                ->defaultsIfMissing(true) // Shouldn't work; boolean overrides
                                          // defaultsIfMissing()
            ->option('v')
                ->alias('verbose')
                ->value()
                    ->optional()
                    ->defaults(2)
                    ->defaultsIfMissing(-1)
            ->argv()
            ;

        $this->assertFalse($options->debug);
        $this->assertEquals(-1, $options->v);
        $this->assertEquals(-1, $options->verbose);
    } // end testDefaultsIfMissing ()

    /**
     * Tests requiredIfNull.
     * @expectedException org\destrealm\utilities\optionally\OptionallyOptionsException
     * @return [type]
     */
    public function testRequiredIfNull ()
    {

/*        $options = Optionally::options(array('--on'))
            ->option('on')
                ->boolean()
                ->requiredIfNull('off')
            ->option('off')
                ->boolean()
                ->requiredIfNull('on')
            ->argv()
            ;

        $this->assertTrue($options->on);
        $this->assertFalse($options->off);*/

        $options = Optionally::options(array())
            ->option('on')
                ->boolean()
                ->requiredIfNull('off')
            ->option('off')
                ->boolean()
                ->requiredIfNull('on')
            ->argv()
            ;

    } // end testRequiredIfNull ()

} // end OptionallyTest
