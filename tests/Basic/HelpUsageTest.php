<?php

namespace DESTRealm\Optionally\Tests\Basic;

use DESTRealm\Optionally\Tests\Common\HelpUsageTestCase;

class HelpUsageTest extends HelpUsageTestCase
{
    public function setUp ()
    {
        require 'autoload.php';
        \DESTRealm\Optionally\Autoloader::load();
    } // end setUp ()
} // end HelpUsageTest ()