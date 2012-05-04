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
                ->boolean()
                ->describe('This option will attempt to enable debugging.')
            ->argv()
            ;

        $this->assertEquals(
'Usage: ./script.php [options]

--debug  This option will attempt to enable debugging.
',
            $options->help()
        );
    } // end testSimpleUsage ()

    public function testMultipleOptions ()
    {
        $options = Optionally::options(array(
            './script.php',
            '--debug',
            '-c',
            '--with-stdout',
            '--without-stderr',
        ))
            ->option('debug')
                ->alias('d')
                ->boolean()
                ->describe('This option enables debugging mode for the script.')
            ->option('config')
                ->alias('c')
                ->value('')
                ->describe('This option instructs the script which configuration
                    file to use if %@file is provided.')
            ->option('with-stdout')
                ->boolean()
                ->describe('Enable STDOUT output.')
            ->option('without-stdout')
                ->boolean()
                ->describe('Disable STDOUT output.')
            ->option('with-stderr')
                ->boolean()
                ->describe('Enable STDERR output.')
            ->option('without-stderr')
                ->boolean()
                ->describe('Disable STDERR output.')
            ->argv()
            ;
            print $options->help();

        $this->assertEquals(
'Usage: ./script.php [options]

--config[=][file]  This option instructs the script which configuration file to
    -c [file]      use if [file] is provided.

--debug            This option enables debugging mode for the script.
    -d

--with-stderr      Enable STDERR output.

--with-stdout      Enable STDOUT output.

--without-stderr   Disable STDERR output.

--without-stdout   Disable STDOUT output.
',
            $options->help()
        );
    } // end testMultipleOptions ()
} // end HelpUsageTestCase