<?php

namespace DESTRealm\Optionally\Tests\Phar;

use DESTRealm\Optionally\Tests\Common\OptionallyTestCase;

class OptionallyTest extends OptionallyTestCase
{
    public function setUp ()
    {
        require 'optionally.phar';
    }
} // end OptionallyTest ()