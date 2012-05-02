<?php

namespace DESTRealm\Optionally\Tests\Basic;

use DESTRealm\Optionally\Tests\Common\StringTestCase;

class StringTest extends StringTestCase
{
    public function setUp ()
    {
        require 'autoload.php';
        \DESTRealm\Optionally\Autoloader::load();
    } // end setUp ()
} // end StringTest ()