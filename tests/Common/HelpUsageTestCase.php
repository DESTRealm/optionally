<?php

namespace DESTRealm\Optionally\Tests\Common;

use DESTRealm\Optionally\Tests\BaseTestCase;
use DESTRealm\Optionally;

class HelpUsageTestCase extends BaseTestCase
{

    public function testSimpleUsage ()
    {
        $options = Optionally::options(array('./script.php', '--debug'))
            ->option('debug')
                ->describe('This option will attempt to enable debugging.')
            ->argv();

        $this->assertEquals(
'Usage: ./script.php [options]

--debug  This option will attempt to enable debugging.
',
            $options->help()
        );
    } // end testSimpleUsage ()
} // end HelpUsageTestCase