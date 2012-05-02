<?php

namespace DESTRealm\Optionally\Tests\Basic;

use DESTRealm\Optionally\Tests\Common\OptionallyTestCase;

class OptionallyTest extends OptionallyTestCase
{
    public function setUp ()
    {
        require 'autoload.php';
        \DESTRealm\Optionally\Autoloader::load();
    } // end setUp ()
} // end OptionallyTest ()