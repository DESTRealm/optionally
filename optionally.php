<?php

/**
 * Optionally master include script.
 *
 * This is the file you should include in order to use Optionally. Everything it
 * needs will be initialized here.
 *
 * Copyright (c) 2012 Benjamin A. Shelton
 *
 * Permission is hereby granted, free of charge, to any person obtaining a
 * copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 */

namespace DESTRealm\Optionally;

$path = dirname(__FILE__).DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.
    'Optionally'.DIRECTORY_SEPARATOR;

require $path.'Exceptions'.DIRECTORY_SEPARATOR.'OptionallyException.php';
require $path.'Exceptions'.DIRECTORY_SEPARATOR.'GetoptException.php';
require $path.'Exceptions'.DIRECTORY_SEPARATOR.'MissingArgvException.php';
require $path.'Exceptions'.DIRECTORY_SEPARATOR.'OptionsException.php';
require $path.'Exceptions'.DIRECTORY_SEPARATOR.'OptionsValueException.php';
require $path.'Getopt'.DIRECTORY_SEPARATOR.'Getopt.php';
require $path.'Options.php';
require $path.'Optionally.php';
require $path.'Help.php';
require $path.'String.php';
