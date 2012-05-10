<?php

namespace DESTRealm\Optionally\Tests\Common;

use DESTRealm\Optionally\String;
use DESTRealm\Optionally\Tests\BaseTestCase;

// Stop PHPUnit's test reports from complaining.
//date_default_timezone_set('UTC');

/**
 * Optionally unit tests.
 *
 * While these unit tests are fairly simple, they serve to demonstrate much of
 * the common use cases Optionally is intended to fulfill.
 */
class StringTestCase extends BaseTestCase
{

    public function testIndent ()
    {
        $string = "This is a space test;\n\nIt should be well indented:\nFour spaces per line.";

        $this->assertEquals(
            '    This is a space test;

    It should be well indented:
    Four spaces per line.',
            String::indent($string, 4)
        );
    } // end testIndent ()

    public function testReplaceIndent ()
    {
        $string = 
'        Oh, indented text,
        I have to replace your space,
        other random cruft.';

        $this->assertEquals(
            '--debug Oh, indented text,
        I have to replace your space,
        other random cruft.',
            String::replaceIndent(
                '        ', '--debug', $string
            )
        );

        $this->assertFalse(
            String::replaceIndent('    ', '--debug', 'String without indentation.')
        );
    } // end testReplaceIndent ()

    public function testBasicStringWrap ()
    {
        $string = 
            'This string should wrap at or before the 80th column. At this point, a forced new line should appear.';

        $this->assertEquals(
            "This string should wrap at or before the 80th column. At this point, a forced\nnew line should appear.",
            String::wrap($string)
        );

        $string = 
            'This string should wrap at or before the 80th column. At this point, a forced-new line should appear.';

        $this->assertEquals(
            "This string should wrap at or before the 80th column. At this point, a forced-\nnew line should appear.",
            String::wrap($string)
        );
    } // end testBasicStringWrap ()

    public function testAdvancedStringWrap ()
    {

        $string =
            'This is just a single sentence.

As you can tell, much of this can span several lines. The intent being, of course, that word-wrap should get triggered.

If it doesn\'t, then we know there\'s a problem.';

        $this->assertEquals(
            'This is just a single sentence.

As you can tell, much of this can span several lines. The intent being, of
course, that word-wrap should get triggered.

If it doesn\'t, then we know there\'s a problem.',
            String::wrap($string)
        );

        $string =
            'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.';

        $this->assertEquals(
            'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor
incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis
nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.
Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu
fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in
culpa qui officia deserunt mollit anim id est laborum.',
            String::wrap($string)
        );

    } // end testAdvancedStringWrap ()

    public function testIndentStringWrap ()
    {
        $string = 'Bacon ipsum dolor sit amet <file> chuck turkey dolore pork
chop duis. Commodo meatloaf quis brisket culpa. Veniam
shoulder filet mignon ut, laboris in beef ribs adipisicing
culpa pastrami pork belly minim sirloin ea jowl. Irure
<file> pariatur in, pork belly frankfurter tri-tip
hamburger ut deserunt meatball minim boudin sunt.
Exercitation minim tongue, corned beef short loin pig
meatloaf shankle andouille aute filet mignon hamburger
voluptate drumstick ut. Deserunt pork proident, turkey
pariatur bacon anim biltong velit magna ex occaecat <file>.
';
        $this->assertEquals(
'            Bacon ipsum dolor sit amet <file> chuck turkey dolore pork
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
            String::indent($string, 12)
        );


    } // end testIndentStringWrap ()

    public function testNormalize ()
    {
        $string =
            "This is a\n    normalized string. Excessive\n        spaces are removed.\nFurthermore,\nunwanted newlines are also\n    removed.\n\nMultiple newlines\n are retained.";

        $this->assertEquals(
            'This is a normalized string. Excessive spaces are removed. Furthermore, unwanted newlines are also removed.

Multiple newlines are retained.',
            String::normalize($string)
        );

        $string =
            "This is a\n\n    normalized string with\n\n    multiple newlines.";

        $this->assertEquals(
            "This is a\n\nnormalized string with\n\nmultiple newlines.",
            String::normalize($string)
        );
    } // end testNormalize ()

    public function testNormalizeLineEndings ()
    {
        $string = "String with\r\nmixed line endings.\r";

        $this->assertEquals(
            "String with\nmixed line endings.\n",
            String::normalizeLineEndings($string)
        );
    } // end testNormalizeLineEndings ()

    public function testNormalizeWrap ()
    {

        $this->assertEquals(
            'This is a test of a string that spans multiple lines and has been filtered by
normalize(). This string will then be passed through to wrap() for further
testing.',
            String::wrap(String::normalize(
                "This is a test of a string that spans multiple lines\n    and has been filtered by normalize(). This string will then be passed through to wrap()\n    for further testing."
            ))
        );

    } // end testNormalizeWrap ()

} // end StringTestCase
