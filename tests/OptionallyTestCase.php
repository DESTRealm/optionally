<?php

namespace DESTRealm\Optionally\Tests;

use DESTRealm\Optionally\Optionally;

/**
 * Master test case class for Optionally tests.
 */
abstract class OptionallyTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * Override global state when running tests with process isolation.
     * Credit: http://matthewturland.com/2010/08/19/process-isolation-in-phpunit/
     */
    /*public function run (\PHPUnit_Framework_TestResult $result=null)
    {
        $this->setPreserveGlobalState(false);
        return parent::run($result);
    } // end run ()*/
} // end OptionallyTestCase