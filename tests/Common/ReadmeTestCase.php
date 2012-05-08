<?php

namespace DESTRealm\Optionally\Tests\Common;

use DESTRealm\Optionally;
use DESTRealm\Optionally\Tests\BaseTestCase;

//error_reporting(E_ALL | E_NOTICE | E_STRICT);

// Stop PHPUnit's test reports from complaining.
//date_default_timezone_set('UTC');

/**
 * Optionally unit tests for README validation.
 *
 * This unit test case includes all source examples currently lists in the
 * README for validation. Be aware that due to the nature of projects and how
 * asynchronous changes can crop up, this test case may lag behind the README
 * by a few commits.
 */
class ReadmeTestCase extends BaseTestCase
{

    /**
     * This test should match up with the sources under the "Basic Usage"
     * heading.
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

} // end ReadmeTestCase