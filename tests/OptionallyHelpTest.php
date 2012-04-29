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
class OptionallyHelpTest extends PHPUnit_Framework_TestCase
{

    public function testBasicHelp ()
    {
        $help = new OptionallyHelp();

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
            "--debug  This option will attempt to enable debugging.
    -d\n",
            $help->help()
        );
    } // end testBasicHelp ()

    public function testHelpWordWrap ()
    {
        $help = new OptionallyHelp();

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
'--debug  This option will attempt to enable debugging. Debugging mode enables
    -d   additional output that may be of some use to developers. Debugging mode
         is rather chatty and may only be of use in circumstances where the
         default output is not enough to diagnose a problem.
',
            $help->help()
        );
    } // end testHelpWordWrap ()

    public function testMultipleOptions ()
    {
        $help = new OptionallyHelp();
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
'--debug    This option will attempt to enable debugging. Debugging mode enables
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

    public function testNamedArg ()
    {
        $help = new OptionallyHelp();

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
'--config[=]<file>  Loads the configuration specified by <file>. <file> is
    -c <file>      required.',
            $help->help()
        );
    } // end testNamedArg ()
} // end OptionallyHelpTest
