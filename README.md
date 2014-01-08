# Optionally

Optionally is a command line library not unlike substack's wonderful Node JS
utility [Optimist](https://github.com/substack/node-optimist). Written for
PHP5.3 and greater, Optionally provides many useful features to filter, test,
and manipulate command line options and alleviates you of much of the mundane
work. If you're familiar with Optimist, you'll find a friend in Optionally, and
although the semantics differ from substack's Optimist they shouldn't be a
nuisance. If you've never used a semantic `getopt()` wrapper before, you'll be
in for a pleasant surprise.

## Motivation

`getopt()` (and, by extension, PEAR's `Console_GetOpt`) has its uses and is
trivial to learn. Perhaps most importantly, learning `getopt()` is highly useful
and can be translated virtually unmodified to many other platforms.
Unfortunately, if you've written more than a handful of fairly trivial shell
scripts, you'll quickly find yourself writing (and re-writing!) code to check
argument values, the presence of specific arguments (or their absence), handle
errors, and document usage. All of this distracts you from the important things:
Writing useful code.

Optionally greatly simplifies the chore of handling command line arguments and
streamlines the process of writing shell scripts in PHP. You'll see why.

## Installation

If you're using Composer (which you should), simply add
`"destrealm/optionally": ">=0.2.0"` or `"destrealm/optionally": "dev-master"` to
your `composer.json`'s `require` and follow the remained of this guide.

Other means of installation, including more manual ones, are left as an exercise
to the reader.

## Prepare Yourselves

To get Optionally working, all you have to do is this:

```php
<?php

use DESTRealm\Optionally;

$optionally = new Optionally();
$options = $optionally
    ->argv();

// Or via static members...

$options = Optionally::options()
    ->argv();
```

...and that's it!

Well, okay. That's not *quite* everything. You'll probably want to do something
fancy like process your command line arguments and such. Read on!

## Basic Usage

First, something to note. In each of these examples, we'll be grabbing the
command line arguments by calling Optionally's static method `options()`. You
don't have to do this--it's perfectly reasonable to instantiate a new
`Optionally` object and call the `argv()` method directly, retrieving an
`Options` object that way.

For the following examples, let's assume we have a script that we've run
accordingly:

```
php -q script.php --test=1 -v --debug file.txt output.txt
```

Now, if we grab the `Options` object by doing this:

```php
<?php

use DESTRealm\Optionally;

$options = Optionally::options()
    ->argv();
```
Our options object will contain positional data for `file.txt` and
`output.txt`:

```php
<?php

print $options->args(0); // outputs file.txt
print $options->args(1); // outputs output.txt
```

This means that each positional argument can be accessed from `$options->args()`.
Of course, if you'd rather manipulate the positional arguments yourself or as an
array, you can do that, too:

```php
<?php

$args = $options->args();
print $args[0]; // outputs file.txt
print $args[1]; // outputs output.txt
```

Note: Optionally will probably use the appropriate `Spl*` classes in a future
revision to make it easier to access the `Options` object as an array.

At this juncture, an astute reader might have noticed the `argv()` method call
at the end of our earlier example code. This instructs Optionally that it
shouldn't expect anything more from your code and that it's OK to return an
`Options` object. You must call `argv()` when you're finished setting Optionally
up, no excuses. The reason for this is mostly a mix of asthetics and internal infrastructure (okay, it's mostly internal infrastructure); calling `argv()`
when you're finished setting up options front-loads the processing and makes
capturing exceptions (seen later in this README) easier.

You've probably also taken note that I didn't do anything with the options yet,
and there's a reason: Optionally doesn't know anything about them! Optionally
uses a modified version of PEAR's `Console_GetOpt internally` and puts absolutely
no effort into parsing the command line for options--beyond what you've told it.

Let's give it some options. All of the following examples will assume that
you've made the appropriate declarations to include Optionally in your code.

## Optionally and Options

Command line options are commonly broken down into two groups: Boolean options,
that is options that either exist or not, and options that have values. Boolean
options are things like verbose output flags, debug mode, or anything that can
toggle. Value options are somewhat less common than boolean options, but have
more utility and can specify things like IP addresses, default values for your
script's behavior, and so forth.

In its current incantation, Optionally won't bother much with options even if
you specify them until you tell it what to treat an option as, though this may
change in the future. Thus, in our example:

```
php -q script.php --test=1 -v --debug file.txt output.txt
```

We would need to do the following to extract useful information out of
Optionally:

```php
<?php

$options = Optionally::options()
    ->option('test')
        ->value()       // Tells Optionally the option expects a value.
    ->option('v')
        ->boolean()     // Tells Optionally that the option is boolean (true/false).
    ->option('debug')
        ->boolean()
    ->argv()            // Get the options object.
    ;
```

Now, our `$options` variable will contain:

```php
<?php

var_dump($options->test); // outputs string(1) "1"
var_dump($options->v); // outputs bool(true)
var_dump($options->debug); // outputs bool(true)
```

If, for example, we hadn't passed `--debug` into our script, *boolean* options
take care of this for us:

```php
<?php

// Command line:
// php -q script.php -v --test=1 file.txt

$options = Optionally::options()
    ->option('test')
        ->value()
    ->option('v')
        ->boolean()
    ->option('debug')
        ->boolean()
    ->argv()
    ;

var_dump($options->test); // outputs string(1) "1"
var_dump($options->v); // outputs bool(true)
var_dump($options->debug); // outputs bool(false)
```

Likewise, omitting the `--test=1` option will yield:

```php
<?php

var_dump($options->test); // outputs NULL
```

## The Options Object

As you've seen so far, the `Options` object is what you get returned to you
whenever you call `argv()` on Optionally's method chain. However, the `Options`
object does a few interesting things to make things more PHP-ish. First, all
options exist as pseudo-properties of the `Options` object, so whenever we
call something like:

```php
<?php

print $options->debug;
```

We're actually asking `Option` if it knows about an option named **debug** and,
if it does, what **debug**'s value is. If it doesn't know anything about
**debug**, it'll simply return null which provides you with a means to quickly
check for the existence (or not) of any given option.

However, long options--as they are known in `getopt()` parlance--can often have
hyphens separating word components to make them more readable to humans. This
means that options like `--without-foo` or `--with-bar` might appear with a
certain degree of regularity. The `Options` object provides you with two ways
of dealing with these types of options: Camel case or underscores. Whichever you
use is entirely up to you:

```php
<?php

// Command line:
// php -q script.php --without-foo

$options = Optionally::options()
    ->option('with-bar')
        ->boolean()
    ->option('without-foo')
        ->boolean()
    ->argv()
    ;

var_dump($options->withoutFoo); // outputs bool(true)
var_dump($options->without_foo); // outputs bool(true)
var_dump($options->withBar); // outputs bool(false)
var_dump($options->with_bar); // outputs bool(false)
```

Of course, options that don't contain a hyphen are left as is.

## Advanced Options: Aliases!

Oftentimes, options will have multiple synonyms or aliases. For most scripts,
`-v` and `--verbose` might have the same meaning. Optionally handles this
for you for free:

```php
<?php

// Command line:
// php -q script.php -v

$options = Optionally::options()
    ->option('v')
        ->boolean()
        ->alias('verbose')
    ->argv()
    ;

var_dump($options->v); // outputs bool(true)
var_dump($options->verbose); // outputs bool(true)
```

Order isn't important: Both long and short options can appear in either the
`option()` or `alias()` declarations; neither does the appearance of type
declarations like `boolean()` disrupt Optionally's behavior. As long as you
remember to declare an option with `option()` first, everything will be fine!

```php
<?php

// Command line:
// php -q script.php --debug

$options = Optionally::options()
    ->option('debug')
        ->alias('d')
        ->boolean()
    ->option('v')
        ->boolean()
        ->alias('verbose')
    ->argv()
    ;
```

Incidentally, the same goes for the *Options* object:

```php
<?php

var_dump($options->debug); // outputs bool(true)
var_dump($options->d) ; // alias, outputs bool(true)
var_dump($options->v); // outputs bool(false)
var_dump($options->verbose); // outputs bool(false)
```

## Advanced Options: Optional Values!

Value options aren't necessarily always in need of values. Sometimes values
should be optional and options should have some intrinsic value even if they
weren't specified. Optional values (and optional options) can be handled rather
simply:

```php
<?php

// Command line:
// php -q script.php -v --number --count=5

$options = Optionally::options()
    ->option('v')
        ->alias('verbose')
        ->boolean()
    ->option('number')
        ->alias('n')
        ->value(0)
    ->option('count')
        ->value(0)
    ->option('max')
        ->value(0)
    ->argv()
    ;

var_dump($options->v); // outputs bool(true)
var_dump($options->verbose); // outputs bool(true)
var_dump($options->number); // outputs int(0)
var_dump($options->n); // outputs int(0)
var_dump($options->count); // outputs string(1) "5"
var_dump($options->max); // outputs int(0)
```

You may notice that an option doesn't have to be specified when supplying an
argument to `value()`, and even if an option is specified it doesn't need to
have a value because a default one can be assigned. This means that the
following code could be used to determine if an option was specified or not and
if it had a value assigned to it (notice the empty string):

```php
<?php

$options = Optionally::options()
    ->option('count')
        ->alias('c')
        ->value('')
    ->argv()
    ;

if ($options->count === '') {

    // count wasn't specified on the command line or it was specified without a
    // value.

} else {

    // count was specified and it has a value assigned.

}
```

## Mostly Advanced Feature: Self Documenting!

Optionally will also, optionally (see what I did there?), generate usage
documentation that can be printed under whatever conditions you desire. Perhaps
you want your script to generate usage text whenever it's run without any
arguments or if the user provides `--help` or `-h` options. This is really easy
to do, and the best part is that the documentation is built-in as a feature that
also helps remind *you* what options are intended to do what! Here's an example:

```php
<?php

$options = Optionally::options()
    ->option('debug')
        ->alias('d')
        ->describe('Enables debugging mode for the script. This will generate
            extra output that might be useful for diagnosing problems')
    ->option('verbose')
        ->alias('v')
        ->describe('Enables verbose output. Verbose output is a little more
            chatty than regular output but might also provide useful
            information!')
    ->argv()
    ;

// Generate the help if no arguments are specified:
$args = $options->args();
if (count($args) === 0) {
    print $options->help();
}
```

This will generate the following text:

```
Usage: test2.php [options]

--debug    Enables debugging mode for the script. This will generate extra
    -d     output that might be useful for diagnosing problems

--verbose  Enables verbose output. Verbose output is a little more chatty than
    -v     regular output but might also provide useful information!
```

As you can see, both your options *and* their aliases are provided in the
generated help output. Moreover, though, options with arguments can also be
provided!

```php
<?php

$options = Optionally::options()
    ->option('config')
        ->value() // Require argument.
        ->describe('Reads the configuration file from %arg.')
    ->option('output')
        ->value('')
        ->describe('Prints output to STDIN or to %arg, if provided.')
    ->argv()
    ;
```

This snippet of code will generate the following help text:

```
Usage: test2.php [options]

--config[=]<value>  Reads the configuration file from <value>.

--output[=][value]  Prints output to STDIN or to [value], if provided.
```

As you can see, options that accept require or optional arguments will generate
slightly different help text indicating that they require options. Square
brackets indicate optional items; angle brackets indicate required items. In
both cases, the equal signs can be omitted (`--config=value` is just as valid
as `--config value`), but in the former case, `--config` requires an argument
whle `--output` does not.

Note that by default, if you don't name an argument, the string `value` will be
used in its place. Fortunately, naming arguments is easy! The previous example
demonstrates that `%arg` makes a useful placeholder for printing the argument's
name (you can also use `%@` as a placeholder to save some typing). Incidentally,
you can also use a special shortcut to act as both a text placeholder and as a
means of naming your options with `%@<name>`. Here's an example:

```php
<?php

$options = Optionally::options()
    ->option('config')
        ->value() // Require argument.
        ->describe('Reads the configuration file from %@file.')
    ->option('output')
        ->value('')
        ->describe('Prints output to STDIN or to %@file, if provided.')
    ->argv()
    ;
```

Now our output reads:

```
Usage: test2.php [options]

--config[=]<file>  Reads the configuration file from <file>.

--output[=][file]  Prints output to STDIN or to [file], if provided.
```

Of course, if the `%@<name>` convention is too awkward for you, the `describe()`
method will also accept the argument name as its second argument:

```php
<?php

$options = Optionally::options()
    ->option('config')
        ->value() // Require argument.
        ->describe('Reads the configuration file from %@.', 'file')
    ->option('output')
        ->value('')
        ->describe('Prints output to STDIN or to %@, if provided.', 'file')
    ->argv()
    ;
```

This will generate the same output as the previous example.

## Really Advanced Options: Mostly Optional Values with Different Defaults!

`value()` isn't the only way to specify defaults but it is the easiest.
Unfortunately, it doesn't work if your specific needs require that an option
assume different values if it has a value passed to it, has no value passed to
it, or isn't specified at all. In the case of our `--count` option, we might
want to assign `FALSE` if the option wasn't specified, `0` if it was
specified but no value was passed to it, or whatever the value was if the user
was kind enough to supply us with such things.

In other words:

    # "count" should be FALSE
    php -q script.php

    # count should be 0
    php -q script.php --count

    # count should be 15
    php -q script.php --count 15


To do this, we'll need to use the two alternate methods of supplying defaults
that Optionally uses: `defaults()` and `defaultsIfMissing()`. We'll also need
to use a new method `optional()` to tell Optionally that our value is now
an optional one:

```php
<?php

$options = Optionally::options()
    ->option('count')
        ->value()
            ->optional()    // tells Optionally that this value is optional; required for this example
            ->defaults(0)   // sets default to 0
            ->defaultsIfMissing(false)    // sets default to false if --count is missing
    ->argv()
    ;
```

We can't specify `0` or `false` when we call `value()`, because
neither `defaults()` nor `defaultsIfMissing()` do anything if the default
value has already been set by `value()`. Thus, we must ignore passing a value
to `value()` and use the extra methods to gain more fine-grained control over
what we want Optionally to do.

## Really Advanced Options: Countable Options and Array Options!

For certain use cases, it might be handy to have Optionally count the number of
times an option was specified on the command line. To illustrate, suppose you
have an application that generates increasinly more verbose output for each
instance the user specifies `-v` on the command line. In this case, you might
want to have a count of the number of times `-v` appears. Fortunately, this is
easy with the `isCountable()` or `countable()` methods:

```php
<?php

// Command line:
// php -q script.php -v -v file.txt

$options = Optionally::options()
    ->option('verbose')
        ->alias('v')
        ->isCountable() // countable() is an alias to this.
    ->argv()
    ;

var_dump($options->verbose); // outputs string(1) "2"
var_dump($options->v);       // outputs string(1) "2"
```

Now, if someone were to specify `php -q yourscript.php -v -v -v -v`,
`print $options->v` or `print $options->verbose` would output **4** instead of
2 as in our example!

In other situations, you might want to cumulatively gather the values of each
successive appearance of a command line option rather than counting it. To do
this, Optionally provides the `isArray()` method. This might be useful if you're
writing an ImageMagick script that can run multiple filters on the same image
depending on what the user specifies:

```php
<?php

// Command line:
// php -q script.php --filter bw --filter mosaic

$options = Optionally::options()
    ->option('filter')
        ->isArray()
    ->argv()
    ;

var_dump($options->filter); // outputs array(2){[0]=> string(2) "bw" [1]=> string(6) "mosaic"}
```

Elements that are flagged with `isArray()` will return an array of values if the
option was specified with one or more values or `null` if the user was confused,
provided the option, but didn't supply an argument.

In most cases, you should be able to squeak by if you simply check to see if
the option's value is an array (or not) and treat it accordingly:

```php
<?php

if ((array)$options->filter === $options->filter) {
    // $options->filter is an array...
} else {
    // $options->filter is definitely jacked up.
}
```

## Really Advanced Options: Test Option Values!

Optionally conveniently provides you, dear programmer, with a means of testing
(or filtering) options supplied by the user for validity and discarding or
replacing those that happen to fail your validity checks. This might be useful
if there's a specific option (or two) that must be supplied a number, string, or
other pattern and there's some chance the user might screw up. Generally
speaking, anything you can match with a regular expression is fair game.

Currently, there's two methods to filter or test values supplied to your
options fittingly named `filter()` and `test()`. While `filter()` and `test()`
are just a means to the same end, don't be lulled into believing they operate
identically! Both accept a single argument, your callback function, but the
similarities end there!

The callback function you supply to `filter()` accepts one (and only one!)
argument: That's the value for the option it's attached to. This function must
then examine the value it's passed and either return it unscathed or alter it
until it matches something you want.

In contrast, the callback function you supply to `test()` is a bit more
complicated: It accepts one or two values, depending on whether you need a
default, and can raise an exception if you don't. Furthermore, the callback
function you supply must return a boolean--either `true` or `false`--indicating
that the value it was passed matches what your code expects or doesn't and needs
to be handled accordingly. The second argument instructs Optionally to replace
those values that fail with something more appropriate.

Since `filter()` is the most simplistic of the two, we'll first examine an
example of it in action. We'll demonstrate an argument that expects integers
*only* and converts everything that isn't an integer *to* an integer:

```php
<?php

$options = Optionally::options()
    ->option('number')
        ->filter(function($value){
            if (is_numeric($value)) {
                return (int)$value;
            }
            return 0;
        })
    ->argv()
    ;
```

As you can see, `filter()` accepts a function that itself takes a single
argument, `$value`, examines the value to determine if it's an integer (and
helpfully casts it just in case), or returns 0 for those values that aren't. If
we were to supply `--number=5.0` on the command line, Optionally would convert
the value of `$options->number` to 0.

Although `test()` is somewhat more complicated in that it accepts two arguments,
its behavior may be more straightforward than `filter()`'s.  Here's an
illustration of a very basic `test()` to match numbers similarly to what we did
in the previous example; anything that doesn't match will cause Optionally to
throw an `OptionsValueException`:

```php
<?php

$options = Optionally::options()
    ->option('number')
        ->value() // Passing a value here will also prevent throwing an OptionsValueException
        ->test(function($value){
            return (bool)preg_match('#[0-9]+#', $value) !== false;
        })
    ->argv()
    ;
```

Again, in this example, if `--number` is supplied anything but a number (an
integer at that!), Optionally will throw an `OptionsValueException`. You'll need
to catch this exception and do something useful with it, such as printing out
the script's usage text or perhaps a descriptive error so the user has an idea
what went wrong.

Testing option values can be much more useful if you decide to use a default
value in those circumstances where the value supplied fails your test. `test()`
lets you do that by supplying the default as its second argument in a manner
that matches almost identically with out `filter()` example above:

```php
<?php

$options = Optionally::options()
    ->option('number')
        ->value()
        ->test(function($value){
                return (bool)preg_match('#[0-9]+#', $value) !== false;
            },
            0)
    ->argv()
    ;

var_dump($options->number); // outputs int(0)
```

Now, if the user supplies anything but an integer, the value of
`$options->number` will be pegged at 0, just like what what happened in our
`filter()` example!

Incidentally, both `filter()` and `test()` will work on array options as
specified by `isArray()`, but **they will not work** on `boolean()` or
`isCountable()` options. Here's an example using test on an option array:

```php
<?php

$options = Optionally::options()
    ->option('filter')
        ->isArray()
        ->test(function($value){return function_exists($value);}, 'scale')
    ->argv()
    ;
```

In this example, **each element** of the options array `filter` will be tested
against the anonymous function supplied to `test()`. This function checks to
see whether each value is a function that exists, and if it's not, it will force
that value to "scale".

Of course, for some functions, simply `filter()`ing the data might be more
appropriate. The following example demonstrates how to convert every argument
supplied to an option array to an integer:

```php
<?php

$options = Optionally::options()
    ->option('filter')
        ->isArray()
        ->filter(function($value){return (int)$value;})
    ->argv()
    ;
```

Of course, anything you can stick in a function can be used to filter the array.
Don't forget that PHP 5.3 now has closures:

```php
<?php

class Converter
{
  public function toInt ($i) { return (int)$i; }
}

$converter = new Converter();

$options = Optionally::options()
    ->option('filter')
        ->isArray()
        ->filter(function($value) use ($converter){return $converter->toInt($value);})
    ->argv()
    ;

```

## Really, Really Advanced Options: Error Handling!

Errors in Optionally are handled by throwing exceptions. Generally speaking,
Optionally tries its best not to throw an exception unless it's absolutely
necessary, but there are some circumstances under which the underlying
GetOpt library may genuinely insist on chucking one out there.

Optionally may throw an exception if you've told it to expect a `->value()` but
the user didn't specify one, if you've used `->requiredIfNull()` or if you're
using `->test()` to filter out invalid values. Conveniently, Optionally will also
throw an exception if you've forgotten to append `->argv()` to your options
list, reminding you that it doesn't know you're finished!

Handling exceptions is easy, and once the help generator is finished, it'll be
even easier:

```php
<?php

try {
    $options = Optionally::options()
        // Setup options.
        ->argv()
        ;
} catch (OptionallyException $e) {
    // Handle the error.
}
```

Of course, if you *really* need to catch a specific exception Optionally
happens to be throwing, feel free to take a peak at `exceptions.php`; they're
all in there, and they're all fairly well documented and indicate what
conditions may trigger them.

## Advanced (but stupid) Options: Required Options!

Normally, you shouldn't torture your users by making an option a required one.
After all, that's why we call them command line **options** because they're
optional. Get it? Options are *optional*? Don't use this feature. Honestly, not
even a little bit. Seriously, just scroll passed this section and pretend it
doesn't exist. If you can't scroll passed this section because I shuffled it
around, just pretend it doesn't exist. Perhaps you should read the license text
instead.

Of course, if you're the masochistic type and intend to write a script
for your use and your use only (that never happens, believe me; if it's useful,
it'll eventually leak out, and you'll send it to someone), you can make sure
that an option *absolutely must be supplied*:

```php
<?php

$options = Optionally::options()
    ->option('require-me')
        ->alias('r')
        ->boolean()
        ->required()    // don't do this
    ->argv()
    ;
```

This will throw an `OptionsException`. You shouldn't be doing this (did I repeat
myself?), so I'll leave it to you to decide how to handle the generated
exception.

Hint: Use default values if you're thinking about using a required option.
They're easier to maintain and much less frustrating for your users.

# License

Optionally is an Optimist- (NodeJS) like API and getopt wrapper for PHP.
Although Optionally isn't a direct decendent of Optimist for reasons mostly
related to quirks in both its author and in PHP, it does adhere to many of the
same principles first introduced in popular usage by Optimist for handling
command line arguments.

Copyright (c) 2012 Benjamin A. Shelton

Permission is hereby granted, free of charge, to any person obtaining a
copy of this software and associated documentation files (the "Software"),
to deal in the Software without restriction, including without limitation
the rights to use, copy, modify, merge, publish, distribute, sublicense,
and/or sell copies of the Software, and to permit persons to whom the
Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
IN THE SOFTWARE.