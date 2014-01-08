<?php

namespace DESTRealm\Optionally\Tests;

/**
 * Optionally unit tests.
 *
 * While these unit tests are fairly simple, they serve to demonstrate much of
 * the common use cases Optionally is intended to fulfill.
 */
class OptionallyTest extends \PHPUnit_Framework_TestCase
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
        $optionally = new Optionally(array('test.php', '--debug', '-c', 'file.config'));
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
        $options = Optionally::options(array('test.php', '--debug', '-c', 'file.config'))
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
     * @expectedException DESTRealm\Optionally\Exceptions\MissingArgvException
     * @expectedExceptionMessage Invalid option "debug." Did you forget to call argv()?
     * @covers DESTRealm\Optionally::__get
     */
    public function testForgottenArgvWarning ()
    {
        $options = Optionally::options(array('test.php', '--debug'))
            ->option('debug')
                ->describe('Enable debugging.')
                ->boolean()
            ;

        $options->optionTemplate; // Override code coverage complaints for __get.

        if ($options->debug) {
            // Nothing happens here.
        }
    } // end testForgottenArgvWarning ()

    /**
     * Test basic option creation.
     */
    public function testCreateOption ()
    {
        $options = Optionally::options(array('test.php', '--file', '-c', 'file', 'test.txt', 'arg1'))
            ->option('c')
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
     * Tests attempting to grab an invalid option from the generated Options
     * object. This should always return null.
     */
    public function testGettingInvalidOption ()
    {
        $options = Optionally::options(array('script.php', '--debug'))
            ->option('debug')
                ->alias('d')
                ->boolean()
            ->argv()
            ;
        $this->assertNull($options->noSuchOption);
    } // end testGettingInvalidOption ()

    /**
     * Tests the creation of an option with its settings passed in as an array.
     * This is likely a lesser-used option since using method chaining is much
     * easier to read but might result in more compact code.
     */
    public function testCreateOptionWithSettingsArray ()
    {
        $options = Optionally::options(array('test.php', '--debug', '-f', 'file.config'))
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
        $options = Optionally::options(array('test.php', '--debug', '-c', 'file', 'arg0'))
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
        $this->assertFalse($options->disableFleece);
        $this->assertEquals('file', $options->c);
        $this->assertEquals('arg0', $args[0]);
    } // end testBoolean ()

    /**
     * Test the creation of options that require values.
     */
    public function testRequiredValues ()
    {
        $options = Optionally::options(array('test.php', '--source=config.txt', '-c', 'file'))
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
     * @expectedException DESTRealm\Optionally\Exceptions\GetoptException
     */
    public function testRequiredValuesShortOptFailure ()
    {
        $options = Optionally::options(array('test.php', '--source=config.txt', '-c'))
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
     * @expectedException DESTRealm\Optionally\Exceptions\GetoptException
     */
    public function testRequiredValuesLongOptFailure ()
    {
        $options = Optionally::options(array('test.php', '--source', '-c', 'file'))
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
     * @expectedException DESTRealm\Optionally\Exceptions\OptionsException
     * @expectedExceptionMessage Required option "f" was not provided!
     * @return [type]
     */
    public function testRequiredOptions ()
    {
        $options = Optionally::options(array('test.php', ))
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
        $options = Optionally::options(array('test.php', '--debug', '-c', 'file'))
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

        $options = Optionally::options(array('test.php', '--debug=file', '-c'))
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

        // Optional values can also be set by supplying a default value to
        // ->value():
        $options = Optionally::options(array('test.php', 'n'))
            ->option('n')
                ->value('')
            ->argv()
            ;

        $this->assertEquals('', $options->n);
    } // end testOptionalValues ()

    /**
     * Tests arguments passed in addition to options.
     */
    public function testArguments ()
    {
        $options = Optionally::options(array('test.php', '--debug', '-c', 'file', '/home', '/usr/bin'))
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

    public function testCountableArguments ()
    {
        $_SERVER['argv'] = array('test.php');
        $options = Optionally::options()
            ->option('c')
                ->isCountable()
            ->argv()
            ;
        $this->assertEquals(0, $options->c);

        $_SERVER['argv'] = array('test.php', '-c');
        $options = Optionally::options()
            ->option('c')
                ->isCountable()
            ->argv()
            ;
        $this->assertEquals(1, $options->c);

        $_SERVER['argv'] = array('test.php', 'arg');
        $options = Optionally::options()
            ->option('c')
                ->isCountable()
            ->argv()
            ;
        $this->assertEquals(0, $options->c);
        $this->assertEquals('arg', $options->args(0));

        $_SERVER['argv'] = array('test.php', '-c', '-c', '-c');
        $options = Optionally::options()
            ->option('c')
                ->isCountable()
            ->argv()
            ;
        $this->assertEquals(3, $options->c);

        $_SERVER['argv'] = array('test.php', '-c', '-c', '-c', '-v', 'arg');
        $options = Optionally::options()
            ->option('c')
                ->isCountable()
            ->option('v')
                ->boolean()
            ->argv()
            ;
        $this->assertEquals(3, $options->c);
        $this->assertTrue($options->v);
        $this->assertEquals('arg', $options->args(0));

        $_SERVER['argv'] = array('test.php', '-v', '-c', '-c', '-c', 'arg');
        $options = Optionally::options()
            ->option('c')
                ->isCountable()
            ->option('v')
                ->boolean()
            ->argv()
            ;
        $this->assertEquals(3, $options->c);
        $this->assertTrue($options->v);
        $this->assertEquals('arg', $options->args(0));

        // Countable alias.
        $_SERVER['argv'] = array('test.php', '-v', '-c', '-c', '-c', 'arg');
        $options = Optionally::options()
            ->option('c')
                ->countable()
            ->option('v')
                ->boolean()
            ->argv()
            ;
        $this->assertEquals(3, $options->c);
        $this->assertTrue($options->v);
        $this->assertEquals('arg', $options->args(0));

    } // end testCountableArguments ()

    public function testArrayArguments ()
    {
        // Array arguments with a single argument.
        $_SERVER['argv'] = array('test.php', '--array', '1');
        $options = Optionally::options()
            ->option('array')
                ->isArray()
            ->argv()
            ;
        $this->assertEquals(array('1'), $options->array);

        // Array arguments with multiple array options.
        $_SERVER['argv'] = array('test.php', '--array', '1', '--array', '2');
        $options = Optionally::options()
            ->option('array')
                ->isArray()
            ->argv()
            ;
        $this->assertEquals(array('1', '2'), $options->array);

        // Array argument with no value specified.
        $_SERVER['argv'] = array('test.php', '--array');
        $options = Optionally::options()
            ->option('array')
                ->isArray()
            ->argv()
            ;
        $this->assertNull($options->array);

        // Array arguments with no value specified.
        $_SERVER['argv'] = array('test.php', '--array', '--array', '--array');
        $options = Optionally::options()
            ->option('array')
                ->isArray()
            ->argv()
            ;
        $this->assertNull($options->array);

        // Array arguments with no value specified, except for the middle
        // value.
        $_SERVER['argv'] = array('test.php', '--array', '--array', '1', '--array');
        $options = Optionally::options()
            ->option('array')
                ->isArray()
            ->argv()
            ;
        $this->assertEquals(array('1'), $options->array);
    } // end testArrayArguments

    public function testUnspecifiedOptions ()
    {
        $options = Optionally::options(array('test.php', '-no-such-option', '-d', 'arg1'))
            ->argv()
            ;

        $this->assertFalse(property_exists($options, 'no_such_option'));
        $this->assertFalse(property_exists($options, 'noSuchOption'));
        $this->assertFalse(property_exists($options, 'd'));

        $this->assertEquals('arg1', $options->args(0));
    } // end testUnspecifiedOptions ()

    /**
     * Tests aliasing assignments.
     */
    public function testAliases ()
    {
        // Simple alias setting.
        $options = Optionally::options(array('test.php', '--debug', '-v'))
            ->option('debug')
                ->alias('d')
                ->boolean()
            ->option('verbose')
                ->alias('v')
                ->boolean()
            ->argv()
            ;
        $this->assertTrue($options->debug);
        $this->assertTrue($options->d);
        $this->assertTrue($options->verbose);
        $this->assertTrue($options->v);

        // Setting aliases with an array.
        $options = Optionally::options(array('test.php', '--debug', '-v'))
            ->option('debug')
                ->alias(array('d', 'D'))
                ->boolean()
            ->option('verbose')
                ->alias(array('v', 'V'))
                ->boolean()
            ->argv()
            ;
        $this->assertTrue($options->debug);
        $this->assertTrue($options->d);
        $this->assertTrue($options->D);
        $this->assertTrue($options->verbose);
        $this->assertTrue($options->v);
        $this->assertTrue($options->V);

        // Advanced aliases.
        $options = Optionally::options(array('test.php',
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
        $options = Optionally::options(array('test.php', '--debug', '--file', 'file.config'))
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
     * Tests setting defaults.
     */
    public function testDefaults ()
    {

        // Default assignments with all options.
        $options = Optionally::options(array('test.php', '--debug', '-v'))
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
        $options = Optionally::options(array('test.php', ))
            ->option('debug')
                ->boolean()
            ->option('v')
                ->alias('verbose')
                ->value()
            ->argv()
            ;

        $this->assertFalse($options->debug);
        $this->assertNull($options->v);
        $this->assertNull($options->verbose);

        // Test default assignment via ->value(). This should override any call
        // to ->defaults(). Furthermore, passing in a value to ->value() should
        // indicate to Optionally that the value is optionally. Both of the
        // following tests should be equivalent.
        $options = Optionally::options(array('test.php', 'n'))
            ->option('n')
                ->value(100)
            ->argv()
            ;

        $this->assertEquals(100, $options->n);

        $options = Optionally::options(array('test.php', 'n'))
            ->option('n')
                ->value(100)
                    ->optional()
            ->argv()
            ;

        $this->assertEquals(100, $options->n);

        $options = Optionally::options(array('test.php', 'n'))
            ->option('n')
                ->value(100)
                    ->optional()
                ->defaults(100)
            ->argv()
            ;

        $this->assertEquals(100, $options->n);

        // The most simplistic test case of an optional value with an intrinsic
        // default is as follows:
        $options = Optionally::options(array('test.php', 'n'))
            ->option('n')
                ->value('')
            ->argv()
            ;

        $this->assertEquals('', $options->n);

    } // end testDefaults ()

    /**
     * Tests assigning defaults with defaultsIfMissing().
     */
    public function testDefaultsIfMissing ()
    {
        // Default assignment with no options and defaultsIfMissing.
        $options = Optionally::options(array('test.php'))
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
     * Tests option handling callbacks.
     */
    public function testCallbacks ()
    {
        $callbacks = array();
        $instance = null;
        $callback = function ($option, $stage, &$reference) use (&$callbacks, &$instance) {
            $callbacks[] = $stage;
            if ($instance === null) {
                $instance = $reference;
            }
        };

        $options = Optionally::options(array('test.php', '-v'))
            ->option('v')
                ->callback($callback)
                ->alias('verbose')
                ->boolean()
                ->describe('This is a description.')
                ->examples('This is an example.')
                ->optional()
            ->argv()
            ;

        $this->assertInstanceOf('DESTRealm\Optionally', $instance);

        $this->assertEquals(
            array(
                'alias',
                'boolean',
                'describe',
                'examples',
                'optional',
                'pre',
                'post'
            ),
            $callbacks
        );

    } // end testCallbacks ()

    /**
     * Tests option handling callbacks.
     */
    public function testCallbacks2 ()
    {
        $callbacks = array();
        $instance = null;
        $callback = function ($option, $stage, &$reference) use (&$callbacks, &$instance) {
            $callbacks[] = $stage;
            if ($instance === null) {
                $instance = $reference;
            }
        };

        $options = Optionally::options(array('test.php', '-v'))
            ->option('c')
            ->option('v')
                ->callback($callback)
                ->alias('verbose')
                ->defaults(5)
                ->defaultsIfMissing(10)
                ->required()
                ->value()
                 ->optional()
            ->argv()
            ;

        //$this->assertTrue($instance);
        $this->assertInstanceOf('DESTRealm\Optionally', $instance);

        $this->assertEquals(
            array(
                'alias',
                'defaults',
                'defaultsIfMissing',
                'required',
                'value',
                'optional',
                'pre',
                'post'
            ),
            $callbacks
        );

    } // end testCallbacks2 ()

    /**
     * Tests setting multiple option handling callbacks.
     * @return [type]
     */
    public function testCallbackArray ()
    {

        $called1 = null;
        $called2 = null;

        $callback1 = function ($option, $stage, $reference) use (&$called1) {
            $called1 = true;
        };
        $callback2 = function ($option, $stage, $reference) use (&$called2) {
            $called2 = true;
        };

        $options = Optionally::options(array('test.php', '-v'))
            ->option('v')
                ->callback($callback1)
                ->callback($callback2)
                ->alias('verbose')
                ->boolean()
            ->argv()
            ;

        $this->assertTrue($called1);
        $this->assertTrue($called2);

    } // end testCallbackArray ()

    /**
     * Tests value test callback.
     */
    public function testOptionTest ()
    {

        $options = Optionally::options(array('test.php', '--number=5', '-t', 'testing'))
            ->option('number')
                ->value()
                ->test(function($value){
                    return (bool)preg_match('#[0-9]+#', $value, $subject);
                })
            ->option('t')
                ->value()
                ->test(function($value){
                    return $value === 'testing';
                })
            ->argv()
            ;

        $this->assertEquals(5, $options->number);
        $this->assertEquals('testing', $options->t);

    } // end testOptionTest ()

    /**
     * Tests value filter callback.
     */
    public function testOptionFilter ()
    {

        $options = Optionally::options(array('test.php', '--number=5', '-t', 'testing'))
            ->option('number')
                ->value()
                ->filter(function($value){
                    if (preg_match('#[0-9]+#', $value, $subject)) {
                        return (int)$value;
                    } else {
                        return 0;
                    }
                })
            ->option('t')
                ->value()
                ->filter(function($value){
                    if ($value === 'testing') {
                        return $value;
                    } else {
                        return 'not testing';
                    }
                })
            ->argv()
            ;
        $this->assertEquals(5, $options->number);
        $this->assertEquals('testing', $options->t);

        $options = Optionally::options(array('test.php', '--number=foo', '-t', 'bar'))
            ->option('number')
                ->value()
                ->filter(function($value){
                    if (preg_match('#[0-9]+#', $value, $subject)) {
                        return (int)$value;
                    } else {
                        return 0;
                    }
                })
            ->option('t')
                ->value()
                ->filter(function($value){
                    if ($value === 'testing') {
                        return $value;
                    } else {
                        return 'not testing';
                    }
                })
            ->argv()
            ;
        $this->assertEquals(0, $options->number);
        $this->assertEquals('not testing', $options->t);

    } // end testOptionFilter ()

    /**
     * @expectedException DESTRealm\Optionally\Exceptions\OptionsValueException
     */
    public function testOptionTestFailure ()
    {
        $options = Optionally::options(array('test.php', '--number=asdf'))
            ->option('number')
                ->value()
                ->test(function($value){
                    return (bool)preg_match('#[0-9]+#', $value) !== false;
                })
            ->argv()
            ;
    } // end testOptionTestFailure ()

    /**
     * Tests value filter/test callback; if the test fails, the default value
     * is returned instead.
     */
    public function testOptionTestValue ()
    {

        $options = Optionally::options(array('test.php', '--number=5', '-t', 'testing'))
            ->option('number')
                ->value()
                ->test(function($value){
                    return (bool)preg_match('#[0-9]+#', $value, $subject) !== false;
                }, 0)
            ->option('t')
                ->value()
                ->test(function($value){
                    return $value === 'testing';
                })
            ->argv()
            ;

        $this->assertEquals(5, $options->number);
        $this->assertEquals('testing', $options->t);

        $options = Optionally::options(array('test.php', '--number=asdf', '-t', 'testing'))
            ->option('number')
                ->value()
                ->test(function($value){
                    return (bool)preg_match('#[0-9]+#', $value, $subject) !== false;
                }, 0)
            ->option('t')
                ->value()
                ->test(function($value){
                    return $value === 'testing';
                })
            ->argv()
            ;

        $this->assertEquals(0, $options->number);
        $this->assertEquals('testing', $options->t);

    } // end testOptionTestValue ()

    /**
     * Attempts to set test() values for options that have been declared as
     * countable. The assertions in here should indicate a failure of the
     * test() to apply the correct values (this is expected).
     * @return [type] [description]
     */
    public function testOptionTestIsCountable ()
    {
        $options = Optionally::options(array('test.php'))
            ->option('c')
                ->isCountable()
            ->test(function($value){return false;}, 10)
            ->argv()
            ;
        $this->assertEquals(0, $options->c);

        $options = Optionally::options(array('test.php', '-c', '-c'))
            ->option('c')
                ->isCountable()
            ->test(function($value){return false;}, 10)
            ->argv()
            ;
        $this->assertEquals(2, $options->c);
    } // end testOptionTestIsCountable ()

    /**
     * Attempts to set filter() values for options that have been declared as
     * countable. The assertions in here should indicate a failure of the
     * filter() to apply the correct values (this is expected).
     */
    public function testOptionFilterIsCountable ()
    {
        $options = Optionally::options(array('test.php'))
            ->option('c')
                ->isCountable()
            ->filter(function($value){return -100;})
            ->argv()
            ;
        $this->assertEquals(0, $options->c);

        $options = Optionally::options(array('test.php', '-c', '-c'))
            ->option('c')
                ->isCountable()
            ->filter(function($value){return -100;})
            ->argv()
            ;
        $this->assertEquals(2, $options->c);
    } // end testOptionFilterIsCountable ()

    /**
     * Tests test() as run on an option array.
     */
    public function testOptionTestIsArray ()
    {
        $options = Optionally::options(array('test.php', '--array', '1'))
            ->option('array')
                ->isArray()
            ->test(function($value){return is_numeric($value);})
            ->argv()
            ;
        $this->assertEquals(array('1'), $options->array);

        $options = Optionally::options(array('test.php', '--array', '1', '--array', '2', '--array', 'three'))
            ->option('array')
                ->isArray()
            ->test(function($value){return is_numeric($value);}, 0)
            ->argv()
            ;
        $this->assertEquals(array('1', '2', '0'), $options->array);

        // No option specified; this should generate an empty array.
        $options = Optionally::options(array('test.php'))
            ->option('array')
                ->isArray()
            ->test(function($value){return is_numeric($value);}, 0)
            ->argv()
            ;
        $this->assertEquals(array(), $options->array);

    } // end testOptionTestArray ()

    /**
     * Tests filter() as run on an option array.
     */
    public function testOptionFilterIsArray ()
    {
        $options = Optionally::options(array('test.php', '--array', '1'))
            ->option('array')
                ->isArray()
            ->filter(function($value){return (int)$value;})
            ->argv()
            ;
        $this->assertEquals(array(1), $options->array);

        $options = Optionally::options(array('test.php', '--array', '1', '--array', '2', '--array', 'three'))
            ->option('array')
                ->isArray()
            ->test(function($value){return is_numeric($value);}, 0)
            ->argv()
            ;
        $this->assertEquals(array(1, 2, 0), $options->array);
    } // end testOptionFilterIsArray ()

} // end OptionallyTestCase
