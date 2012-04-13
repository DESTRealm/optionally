<?php

namespace org\destrealm\utilities\optionally;

use PHPUnit_Framework_TestCase;

require_once 'optionally.php';


// Stop PHPUnit's test reports from complaining.
date_default_timezone_set('UTC');

/**
 * Optionally unit tests.
 *
 * While these unit tests are fairly simple, they serve to demonstrate much of
 * the common use cases Optionally is intended to fulfill.
 */
class StringTest extends PHPUnit_Framework_TestCase
{

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

    } // end testAdvancedStringWrap ()

} // end StringTest
