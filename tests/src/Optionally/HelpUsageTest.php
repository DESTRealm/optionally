<?php

namespace DESTRealm\Optionally\Tests;

class HelpUsageTest extends \PHPUnit_Framework_TestCase
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

        $this->assertTrue($options->thisIsALongOptionOne);
        $this->assertTrue($options->this_is_a_long_option_one);
        $this->assertTrue($options->thisIsALongOption1);
        $this->assertTrue($options->this_is_a_long_option_1);
    } // testLengthyOptions ()

    public function testLengthyDescriptions ()
    {
        $options = Optionally::options(array('script.php'))
            ->option('debug')
                ->alias('d')
                ->boolean()
                ->describe('Bacon ipsum dolor sit amet chuck turkey dolore pork
                    chop duis. Commodo meatloaf quis brisket culpa. Veniam
                    shoulder filet mignon ut, laboris in beef ribs adipisicing
                    culpa pastrami pork belly minim sirloin ea jowl. Irure
                    pariatur in, pork belly frankfurter tri-tip hamburger ut
                    deserunt meatball minim boudin sunt. Exercitation minim
                    tongue, corned beef short loin pig meatloaf shankle
                    andouille aute filet mignon hamburger voluptate drumstick
                    ut. Deserunt pork proident, turkey pariatur bacon anim
                    biltong velit magna ex occaecat.')
            ->option('config')
                ->alias('c')
                ->value()
                ->describe('Bacon ipsum dolor sit amet %@file chuck turkey
                    dolore pork chop duis. Commodo meatloaf quis brisket culpa.
                    Veniam shoulder filet mignon ut, laboris in beef ribs
                    adipisicing culpa pastrami pork belly minim sirloin ea jowl.
                    Irure %@ pariatur in, pork belly frankfurter tri-tip
                    hamburger ut deserunt meatball minim boudin sunt.
                    Exercitation minim tongue, corned beef short loin pig
                    meatloaf shankle andouille aute filet mignon hamburger
                    voluptate drumstick ut. Deserunt pork proident, turkey
                    pariatur bacon anim biltong velit magna ex occaecat %@.')
            ->option('file')
                ->value()
                ->describe('Bacon ipsum dolor sit amet %@file chuck turkey
                    dolore pork chop duis. Commodo meatloaf quis brisket culpa.
                    Veniam shoulder filet mignon ut, laboris in beef ribs
                    adipisicing culpa pastrami pork belly minim sirloin ea jowl.
                    Irure %@ pariatur in, pork belly frankfurter tri-tip
                    hamburger ut deserunt meatball minim boudin sunt.
                    Exercitation minim tongue, corned beef short loin pig
                    meatloaf shankle andouille aute filet mignon hamburger
                    voluptate drumstick ut. Deserunt pork proident, turkey
                    pariatur bacon anim biltong velit magna ex occaecat %@.')
            ->option('test-file')
                ->value()
                ->describe('Bacon ipsum dolor sit amet %@file chuck turkey
                    dolore pork chop duis. Commodo meatloaf quis brisket culpa.
                    Veniam shoulder filet mignon ut, laboris in beef ribs
                    adipisicing culpa pastrami pork belly minim sirloin ea jowl.
                    Irure %@ pariatur in, pork belly frankfurter tri-tip
                    hamburger ut deserunt meatball minim boudin sunt.
                    Exercitation minim tongue, corned beef short loin pig
                    meatloaf shankle andouille aute filet mignon hamburger
                    voluptate drumstick ut. Deserunt pork proident, turkey
                    pariatur bacon anim biltong velit magna ex occaecat %@.')
            ->argv()
            ;

        $this->assertEquals(
'Usage: script.php [options]

--config[=]<file>     Bacon ipsum dolor sit amet <file> chuck turkey dolore pork
    -c <file>         chop duis. Commodo meatloaf quis brisket culpa. Veniam
                      shoulder filet mignon ut, laboris in beef ribs adipisicing
                      culpa pastrami pork belly minim sirloin ea jowl. Irure
                      <file> pariatur in, pork belly frankfurter tri-tip
                      hamburger ut deserunt meatball minim boudin sunt.
                      Exercitation minim tongue, corned beef short loin pig
                      meatloaf shankle andouille aute filet mignon hamburger
                      voluptate drumstick ut. Deserunt pork proident, turkey
                      pariatur bacon anim biltong velit magna ex occaecat <file>.

--debug               Bacon ipsum dolor sit amet chuck turkey dolore pork chop
    -d                duis. Commodo meatloaf quis brisket culpa. Veniam shoulder
                      filet mignon ut, laboris in beef ribs adipisicing culpa
                      pastrami pork belly minim sirloin ea jowl. Irure pariatur
                      in, pork belly frankfurter tri-tip hamburger ut deserunt
                      meatball minim boudin sunt. Exercitation minim tongue,
                      corned beef short loin pig meatloaf shankle andouille aute
                      filet mignon hamburger voluptate drumstick ut. Deserunt
                      pork proident, turkey pariatur bacon anim biltong velit
                      magna ex occaecat.

--file[=]<file>       Bacon ipsum dolor sit amet <file> chuck turkey dolore pork
                      chop duis. Commodo meatloaf quis brisket culpa. Veniam
                      shoulder filet mignon ut, laboris in beef ribs adipisicing
                      culpa pastrami pork belly minim sirloin ea jowl. Irure
                      <file> pariatur in, pork belly frankfurter tri-tip
                      hamburger ut deserunt meatball minim boudin sunt.
                      Exercitation minim tongue, corned beef short loin pig
                      meatloaf shankle andouille aute filet mignon hamburger
                      voluptate drumstick ut. Deserunt pork proident, turkey
                      pariatur bacon anim biltong velit magna ex occaecat <file>.

--test-file[=]<file>  Bacon ipsum dolor sit amet <file> chuck turkey dolore pork
                      chop duis. Commodo meatloaf quis brisket culpa. Veniam
                      shoulder filet mignon ut, laboris in beef ribs adipisicing
                      culpa pastrami pork belly minim sirloin ea jowl. Irure
                      <file> pariatur in, pork belly frankfurter tri-tip
                      hamburger ut deserunt meatball minim boudin sunt.
                      Exercitation minim tongue, corned beef short loin pig
                      meatloaf shankle andouille aute filet mignon hamburger
                      voluptate drumstick ut. Deserunt pork proident, turkey
                      pariatur bacon anim biltong velit magna ex occaecat <file>.
',
            $options->help()
        );
    } // end testLengthyDescriptions ()
} // end HelpUsageTestCase