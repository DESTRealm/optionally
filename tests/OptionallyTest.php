<?php

namespace org\destrealm\utilities;

use PHPUnit_Framework_TestCase;

require_once 'optionally.php';

class OptionallyTest extends PHPUnit_Framework_TestCase
{

    public function testCreateOption ()
    {

        $_SERVER['argv'] = '-c file.config -f';

        $options = new Optionally('script.php -c file --file test.txt arg1');
        $options
            ->option('c')
                ->required()
                ->describe('Loads a config file.')
                ->alias('config')
                ->value()
            ->option('file')
                ->describe('Dumps output into the specified file, if provided, '.
                          'or STDOUT if not.')
                ->alias('f')
                ->value()
                    ->optional()
            ->args(array('First argument', 'Second argument'))
            ;

        print_r($options);

        $this->assertEquals('file.config', $options->c);
        $this->assertNull($options->file);

    } // end testCreateOption ()

} // end OptionallyTest