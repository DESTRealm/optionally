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

    public function testChangingUsageText ()
    {
        $options = Optionally::options(array('./script.php', '--debug'))
            ->usage('%script usage: %script [options]')
            ->option('debug')
                ->boolean()
                ->describe('This option will attempt to enable debugging.')
            ->argv()
            ;

        $this->assertEquals(
'./script.php usage: ./script.php [options]

--debug  This option will attempt to enable debugging.
',
            $options->help()
        );
    } // end testChangingUsageText ()

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

    public function testLengthyOptions ()
    {
        $options = Optionally::options(array(
            'script.php',
            '--this-is-a-long-option-one'
        ))
            ->option('this-is-a-long-option-one')
                ->boolean()
                ->alias('this-is-a-long-option-1')
                ->describe('This option tests lengthy options, their wordwrap,
                    and ensures that the output should be generated as expected.
                ')
            ->option('this-is-a-long-option-two')
                ->boolean()
                ->alias('this-is-a-long-option-2')
                ->describe('This option tests lengthy options, their wordwrap,
                    and ensures that the output should be generated as expected.
                ')
            ->argv()
            ;

        $this->assertEquals(
'Usage: script.php [options]

--this-is-a-long-option-one
    This option tests lengthy options, their wordwrap, and ensures that the
--this-is-a-long-option-1
    output should be generated as expected.

--this-is-a-long-option-two
    This option tests lengthy options, their wordwrap, and ensures that the
--this-is-a-long-option-2
    output should be generated as expected.
',
            $options->help()
        );
    } // testLengthyOptions ()
} // end HelpUsageTestCase