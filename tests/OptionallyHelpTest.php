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
            debugging.', array('d'));

        $this->assertEquals(
            '--debug  This option will attempt to enable debugging.',
            $help->help()
        );
    } // end testBasicHelp ()

} // end OptionallyHelpTest
