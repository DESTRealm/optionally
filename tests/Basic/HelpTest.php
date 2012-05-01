<?php

namespace DESTRealm\Optionally\Tests\Basic;

use DESTRealm\Optionally\Help;
use DESTRealm\Optionally\Tests\OptionallyTestCase;

//error_reporting(E_ALL | E_NOTICE | E_STRICT);

// Stop PHPUnit's test reports from complaining.
//date_default_timezone_set('UTC');

/**
 * Optionally unit tests.
 *
 * While these unit tests are fairly simple, they serve to demonstrate much of
 * the common use cases Optionally is intended to fulfill.
 */
class HelpTest extends OptionallyTestCase
{

    public function testBasicHelp ()
    {
        $_SERVER['argv'] = array('./script.php');

        $help = new Help();

        $help->addDescription('debug', 'This option will attempt to enable
            debugging.');

        $help->setOptions(
            array(
                'debug' => array(
                    'aliases' => array('d'),
                    'required' => false,
                    'ifNull' => '',
                    'boolean' => true,
                    'callback' => null,
                    'filter' => null,
                    'filterValue' => null,
                    'defaults' => null,
                    'examples' => null,
                    'ifMissing' => null,
                    'value' => false,
                    'optionalValue' => false,
                ),
            )
        );

        $this->assertEquals(
'Usage: ./script.php [options]

--debug  This option will attempt to enable debugging.
    -d
',
            $help->help()
        );
    } // end testBasicHelp ()

    public function testHelpWordWrap ()
    {
        $_SERVER['argv'] = array('./script.php');

        $help = new Help();

        $help->addDescription('debug', 'This option will attempt to enable
            debugging. Debugging mode enables additional output that may be of
            some use to developers. Debugging mode is rather chatty and may only
            be of use in circumstances where the default output is not enough to
            diagnose a problem.');

        $help->setOptions(
            array(
                'debug' => array(
                    'aliases' => array('d'),
                    'required' => false,
                    'ifNull' => '',
                    'boolean' => true,
                    'callback' => null,
                    'filter' => null,
                    'filterValue' => null,
                    'defaults' => null,
                    'examples' => null,
                    'ifMissing' => null,
                    'value' => false,
                    'optionalValue' => false,
                ),
            )
        );

        $this->assertEquals(
'Usage: ./script.php [options]

--debug  This option will attempt to enable debugging. Debugging mode enables
    -d   additional output that may be of some use to developers. Debugging mode
         is rather chatty and may only be of use in circumstances where the
         default output is not enough to diagnose a problem.
',
            $help->help()
        );
    } // end testHelpWordWrap ()

    public function testLongArgument ()
    {
        $_SERVER['argv'] = array('./script.php');

        $help = new Help();

        $help->addDescription('this-is-a-really-long-argument', 'This is a long
            argument that should place this usage text on the following line.');

        $help->setOptions(
            array(
                'this-is-a-really-long-argument' => array(
                    'aliases' => array(),
                    'required' => false,
                    'ifNull' => '',
                    'boolean' => true,
                    'callback' => null,
                    'filter' => null,
                    'filterValue' => null,
                    'defaults' => null,
                    'examples' => null,
                    'ifMissing' => null,
                    'value' => false,
                    'optionalValue' => false,
                ),
            )
        );

        $this->assertEquals(
'Usage: ./script.php [options]

--this-is-a-really-long-argument
    This is a long argument that should place this usage text on the following
    line.
',
            $help->help()
        );
    } // end testLongArgument ()

    public function testMultipleOptions ()
    {
        $_SERVER['argv'] = array('./script.php');

        $help = new Help();
        $help->setOptions(array());

        $help->addDescription('debug', 'This option will attempt to enable
            debugging. Debugging mode enables additional output that may be of
            some use to developers. Debugging mode is rather chatty and may only
            be of use in circumstances where the default output is not enough to
            diagnose a problem.');
        $help->addDescription('testing', 'This option enables testing mode.
            Testing mode is similar to debugging mode with the exception that it
            enables experimental features.');
        $help->addDescription('verbose', 'This option enables verbose mode.');

        $help->setOptions(
            array(
                'debug' => array(
                    'aliases' => array('d'),
                    'required' => false,
                    'ifNull' => '',
                    'boolean' => true,
                    'callback' => null,
                    'filter' => null,
                    'filterValue' => null,
                    'defaults' => null,
                    'examples' => null,
                    'ifMissing' => null,
                    'value' => false,
                    'optionalValue' => false,
                ),
                'testing' => array(
                    'aliases' => array('t'),
                    'required' => false,
                    'ifNull' => '',
                    'boolean' => true,
                    'callback' => null,
                    'filter' => null,
                    'filterValue' => null,
                    'defaults' => null,
                    'examples' => null,
                    'ifMissing' => null,
                    'value' => false,
                    'optionalValue' => false,
                ),
                'verbose' => array(
                    'aliases' => array('v'),
                    'required' => false,
                    'ifNull' => '',
                    'boolean' => true,
                    'callback' => null,
                    'filter' => null,
                    'filterValue' => null,
                    'defaults' => null,
                    'examples' => null,
                    'ifMissing' => null,
                    'value' => false,
                    'optionalValue' => false,
                ),
            )
        );

        $this->assertEquals(
'Usage: ./script.php [options]

--debug    This option will attempt to enable debugging. Debugging mode enables
    -d     additional output that may be of some use to developers. Debugging
           mode is rather chatty and may only be of use in circumstances where
           the default output is not enough to diagnose a problem.

--testing  This option enables testing mode. Testing mode is similar to
    -t     debugging mode with the exception that it enables experimental
           features.

--verbose  This option enables verbose mode.
    -v
',
            $help->help()
        );
    } // end testMultipleOptions ()

    public function testRequiredNamedArg ()
    {
        $_SERVER['argv'] = array('./script.php');

        $help = new Help();

        $help->addDescription('config', 'Loads the configuration specified by
            %@. %arg is required.', 'file');

        $help->setOptions(
            array(
                'config' => array(
                    'aliases' => array('c'),
                    'required' => false,
                    'ifNull' => '',
                    'boolean' => true,
                    'callback' => null,
                    'filter' => null,
                    'filterValue' => null,
                    'defaults' => null,
                    'examples' => null,
                    'ifMissing' => null,
                    'value' => true,
                    'optionalValue' => false,
                )
            )
        );

        $this->assertEquals(
'Usage: ./script.php [options]

--config[=]<file>  Loads the configuration specified by <file>. <file> is
    -c <file>      required.
',
            $help->help()
        );
    } // end testRequiredNamedArg ()

    public function testManyRequiredNamedArgs ()
    {
        $_SERVER['argv'] = array('./script.php');

        $help = new Help();

        $help->addDescription('config', 'Loads the configuration specified by
            %@. %arg is required.', 'file');
        $help->addDescription('save-to', 'Saves the generated output to the file
            specified by %@.', 'file');

        $help->setOptions(
            array(
                'config' => array(
                    'aliases' => array('c'),
                    'required' => false,
                    'ifNull' => '',
                    'boolean' => true,
                    'callback' => null,
                    'filter' => null,
                    'filterValue' => null,
                    'defaults' => null,
                    'examples' => null,
                    'ifMissing' => null,
                    'value' => true,
                    'optionalValue' => false,
                ),
                'save-to' => array(
                    'aliases' => array('s', 'save'),
                    'required' => false,
                    'ifNull' => '',
                    'boolean' => true,
                    'callback' => null,
                    'filter' => null,
                    'filterValue' => null,
                    'defaults' => null,
                    'examples' => null,
                    'ifMissing' => null,
                    'value' => true,
                    'optionalValue' => false,
                )
            )
        );

        $this->assertEquals(
'Usage: ./script.php [options]

--config[=]<file>   Loads the configuration specified by <file>. <file> is
    -c <file>       required.

--save-to[=]<file>  Saves the generated output to the file specified by <file>.
    -s <file>
    --save[=]<file>
',
            $help->help());

    } // end testManyRequiredNamedArgs ()

    public function testOptionalNamedArg ()
    {
        $_SERVER['argv'] = array('./script.php');

        $help = new Help();

        $help->addDescription('config', 'Loads the configuration specified by
            %@. %arg is optional.', 'file');

        $help->setOptions(
            array(
                'config' => array(
                    'aliases' => array('c'),
                    'required' => false,
                    'ifNull' => '',
                    'boolean' => true,
                    'callback' => null,
                    'filter' => null,
                    'filterValue' => null,
                    'defaults' => null,
                    'examples' => null,
                    'ifMissing' => null,
                    'value' => true,
                    'optionalValue' => true,
                )
            )
        );

        $this->assertEquals(
'Usage: ./script.php [options]

--config[=][file]  Loads the configuration specified by [file]. [file] is
    -c [file]      optional.
',
            $help->help()
        );
    } // end testOptionalNamedArg ()

    public function testManyOptionalNamedArgs ()
    {
        $_SERVER['argv'] = array('./script.php');

        $help = new Help();

        $help->addDescription('config', 'Loads the configuration specified by
            %@. %arg is optional.', 'file');
        $help->addDescription('save-to', 'Saves the generated output to the file
            specified by %@.', 'file');

        $help->setOptions(
            array(
                'config' => array(
                    'aliases' => array('c'),
                    'required' => false,
                    'ifNull' => '',
                    'boolean' => true,
                    'callback' => null,
                    'filter' => null,
                    'filterValue' => null,
                    'defaults' => null,
                    'examples' => null,
                    'ifMissing' => null,
                    'value' => true,
                    'optionalValue' => true,
                ),
                'save-to' => array(
                    'aliases' => array('s', 'save'),
                    'required' => false,
                    'ifNull' => '',
                    'boolean' => true,
                    'callback' => null,
                    'filter' => null,
                    'filterValue' => null,
                    'defaults' => null,
                    'examples' => null,
                    'ifMissing' => null,
                    'value' => true,
                    'optionalValue' => true,
                )
            )
        );

        $this->assertEquals(
'Usage: ./script.php [options]

--config[=][file]   Loads the configuration specified by [file]. [file] is
    -c [file]       optional.

--save-to[=][file]  Saves the generated output to the file specified by [file].
    -s [file]
    --save[=][file]
',
            $help->help());

    } // end testManyOptionalNamedArgs ()
} // end HelpTest

