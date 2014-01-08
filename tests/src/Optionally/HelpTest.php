<?php

namespace DESTRealm\Optionally\Tests;

use DESTRealm\Optionally\Help;

/**
 * Optionally unit tests.
 *
 * While these unit tests are fairly simple, they serve to demonstrate much of
 * the common use cases Optionally is intended to fulfill.
 */
class HelpTest extends \PHPUnit_Framework_TestCase
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

    public function testSetAliasIndentation ()
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
        $help->setAliasIndentation(2);

        $this->assertEquals(
'Usage: ./script.php [options]

--debug  This option will attempt to enable debugging. Debugging mode enables
  -d     additional output that may be of some use to developers. Debugging mode
         is rather chatty and may only be of use in circumstances where the
         default output is not enough to diagnose a problem.
',
            $help->help()
        );
    } // end testSetAliasIndentation ()

    public function testSetMaxColumns ()
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
        $help->setMaxColumns(100);

        $this->assertEquals(
'Usage: ./script.php [options]

--debug  This option will attempt to enable debugging. Debugging mode enables additional output that
    -d   may be of some use to developers. Debugging mode is rather chatty and may only be of use in
         circumstances where the default output is not enough to diagnose a problem.
',
            $help->help()
        );
    } // end testSetMaxColumns ()

    public function testSetOptionBuffer ()
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
        $help->setOptionBuffer(4);

        $this->assertEquals(
'Usage: ./script.php [options]

--debug    This option will attempt to enable debugging. Debugging mode enables
    -d     additional output that may be of some use to developers. Debugging
           mode is rather chatty and may only be of use in circumstances where
           the default output is not enough to diagnose a problem.
',
            $help->help()
        );
    } // end testSetOptionBuffer ()

    public function testSetOptionCutoff ()
    {
        $_SERVER['argv'] = array('./script.php');

        $help = new Help();

        $help->addDescription('this-is-a-really-long-argument', 'This option
            will attempt to enable debugging. Debugging mode enables additional
            output that may be of some use to developers. Debugging mode is
            rather chatty and may only be of use in circumstances where the
            default output is not enough to diagnose a problem.');

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
        $help->setOptionCutoff(40);

        $this->assertEquals(
'Usage: ./script.php [options]

--this-is-a-really-long-argument  This option will attempt to enable debugging.
                                  Debugging mode enables additional output that
                                  may be of some use to developers. Debugging
                                  mode is rather chatty and may only be of use
                                  in circumstances where the default output is
                                  not enough to diagnose a problem.
',
            $help->help()
        );
    } // end testSetOptionCutoff

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

    public function testDefaultArg ()
    {
        $_SERVER['argv'] = array('./script.php');

        $help = new Help();

        $help->addDescription('config', 'Loads the configuration specified by
            %arg. %arg is required.');

        $help->setOptions(
            array(
                'config' => array(
                    'aliases' => array('c'),
                    'required' => false,
                    'ifNull' => '',
                    'boolean' => false,
                    'callback' => null,
                    'filter' => null,
                    'filterValue' => null,
                    'defaults' => null,
                    'examples' => null,
                    'ifMissing' => null,
                    'optionalValue' => false,
                    'argName' => '',
                    'isArray' => false,
                    'isCountable' => false,
                )
            )
        );

        $this->assertEquals(
'Usage: ./script.php [options]

--config[=]<value>  Loads the configuration specified by <value>. <value> is
    -c <value>      required.
',
            $help->help()
        );
    } // end testDefaultArg ()

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
                    'boolean' => false,
                    'callback' => null,
                    'filter' => null,
                    'filterValue' => null,
                    'defaults' => null,
                    'examples' => null,
                    'ifMissing' => null,
                    'optionalValue' => false,
                    'argName' => '',
                    'isArray' => false,
                    'isCountable' => false,
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
                    'boolean' => false,
                    'callback' => null,
                    'filter' => null,
                    'filterValue' => null,
                    'defaults' => null,
                    'examples' => null,
                    'ifMissing' => null,
                    'optionalValue' => false,
                    'argName' => '',
                    'isArray' => false,
                    'isCountable' => false,
                ),
                'save-to' => array(
                    'aliases' => array('s', 'save'),
                    'required' => false,
                    'ifNull' => '',
                    'boolean' => false,
                    'callback' => null,
                    'filter' => null,
                    'filterValue' => null,
                    'defaults' => null,
                    'examples' => null,
                    'ifMissing' => null,
                    'optionalValue' => false,
                    'argName' => '',
                    'isArray' => false,
                    'isCountable' => false,
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
                    'boolean' => false,
                    'callback' => null,
                    'filter' => null,
                    'filterValue' => null,
                    'defaults' => null,
                    'examples' => null,
                    'ifMissing' => null,
                    'optionalValue' => true,
                    'argName' => '',
                    'isArray' => false,
                    'isCountable' => false,
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
                    'boolean' => false,
                    'callback' => null,
                    'filter' => null,
                    'filterValue' => null,
                    'defaults' => null,
                    'examples' => null,
                    'ifMissing' => null,
                    'optionalValue' => true,
                    'argName' => '',
                    'isArray' => false,
                    'isCountable' => false,
                ),
                'save-to' => array(
                    'aliases' => array('s', 'save'),
                    'required' => false,
                    'ifNull' => '',
                    'boolean' => false,
                    'callback' => null,
                    'filter' => null,
                    'filterValue' => null,
                    'defaults' => null,
                    'examples' => null,
                    'ifMissing' => null,
                    'optionalValue' => true,
                    'argName' => '',
                    'isArray' => false,
                    'isCountable' => false,
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

