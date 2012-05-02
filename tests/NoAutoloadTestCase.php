<?php

namespace DESTRealm\Optionally\Tests;

/**
 * @runInSeparateProcess
 * @runTestsInSeparateProcess
 * @preserveGlobalState disabled
 */
class NoAutoloadTestCase extends \PHPUnit_Framework_TestCase
{
    public function setUp ()
    {
        require 'optionally.php';
    } // end setUp ()
} // end NoAutoloadTestCase