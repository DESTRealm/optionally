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

## Basic Usage

Using Optionally is trivial. First, simply copy (or clone) the repository into a
handy spot (like "lib") and include it:

```php
require_once 'lib/optionally.php';

use org\destrealm\utilities\optionally\Optionally;
```

For every example in this guide, we'll assume that these two lines of code
already exist; you won't see them again, but that doesn't mean they're not
needed.

Next, tell Optionally you'd like to handle your command line arguments (we'll
deal with options later):

```php
$options = Optionally::options()
  ->argv()
  ;
```

This will create an `$options` object that can be used to to get positional
arguments and options. Assuming we ran our script as:

```
php -q script.php --test=1 -v --debug file.txt output.txt
```

Our options object will contain positional data for `file.txt` and
`output.txt`:

```php
print $options->args(0); // outputs file.txt
print $options->args(1); // outputs output.txt
```

This means that each positional argument can be accessed from `$options->args()`.
Of course, if you'd rather manipulate the positional arguments yourself or as an
array, you can do that, too:

```php
$args = $options->args();
print $args[0]; // outputs file.txt
print $args[1]; // outputs output.txt
```

The astute reader might have noticed the `argv()` method call at the end of our
earlier example code. This instructs optionally that it shouldn't expect
anything more from your code and that it's OK to return an `Options` object. You
must call `argv()` when you're finished setting Optionally up, no excuses. The
reason for this is mostly a mix of asthetics and internal infrastructure;
calling `argv()` when you're finished setting up options front-loads the
processing and makes capturing exceptions (seen later in this README) easier.

You've probably also taken note that I didn't do anything with the options yet,
and there's a reason: Optionally doesn't know anything about them! Optionally
uses a modified version of PEAR's `Console_GetOpt internally` and puts absolutely
no effort into parsing the command line for options--beyond what you've told it.

Let's give it some options.

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
$options = Optionally::options()
  ->option('test')
    ->value()       // Tells Optionally the option expects a value.
  ->option('v')
    ->boolean()     // Tells Optionally that the option is boolean (true/false).
  ->option('debug')
    ->boolean()
  ->argv()          // Get the options object.
  ;
```

Now, our `$options` variable will contain:

```php
var_dump($options->test); // outputs string(1) "1"
var_dump($options->v); // outputs bool(true)
var_dump($options->debug); // outputs bool(true)
```

If, for example, we hadn't passed `--debug` into our script, *boolean* options
take care of this for us:

```php

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
var_dump($options->test); // outputs NULL
```

## The Options Object

As you've seen so far, the `Options` object is what you get returned to you
whenever you call `argv()` on Optionally's method chain. However, the `Options`
object does a few interesting things to make things more PHP-ish. First, all
options exist as pseudo-properties of the `Options` object, so whenever we
call something like:

```php
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

var_dump($options->debug); // outputs bool(true)
var_dump($options->d) ; // alias, outputs bool(true)
var_dump($options->v); // outputs bool(false)
var_dump($options->verbose); // outputs bool(false)
```

## Advanced Options: Optional Values!

Value options aren't necessarily always in need of values. Sometimes values
should be optional and have some intrinsic value even if they weren't specified.
Optional values (and optional options) can be handled rather simply:

```php

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
if it had a value assigned to it:

```php

$options = Optionally::options()
  ->option('count')
    ->alias('c')
    ->value('')
  ->argv()
  ;

if ($options->count === '') {

  // count was specified, but it didn't have a value assigned

} else if ($options->count === NULL) {

  // count wasn't specified on the command line.

} else {

  // count was specified and it has a value assigned.

}
```

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
that Optionally uses: `defaults()` and `defaultsIfMissing()`:

```php
$options = Optionally::options()
  ->option('count')
    ->value()
      ->optional()    // tells Optionally that this value is optional; required for this example
      ->defaults(0)   // sets default to 0
      ->defaultsIfMissing(false)    // sets default to false if --count is missing
  ->argv()
  ;
```

We can't specify `0` or `false` when we call `value()` however, because
neither `defaults()` nor `defaultsIfMissing()` do anything if the default
value has already been set by `value()`.

## Really Advanced Options: Test Option Values!

Optionally conveniently provides you, dear programmer, with a means of testing
options supplied by the user, and discarding them if they don't match. This
might be useful if there's a specific option (or two) that must be supplied
numbers, strings, or other patterns. Anything you can match with a regular
expression is fair game:

```php
$options = Optionally::options()
  ->option('number')
    ->value(0)
    ->test(function($value){
      return (bool)preg_match('#[0-9]+#', $value) !== false;
    })
  ->argv()
  ;
```

In this example, if `--number` is supplied anything but a number (an integer at
that!), Optionally will throw an OptionallyOptionsValueException. You'll need to
catch this exception and do something useful with it, such as printing out the
script's usage text or perhaps a descriptive error so the user has an idea what
went wrong.

## Really Advanced Options: Require a Value if Another is Null!

Although the use case for this feature is arguably slim, there might be some
circumstances where you must specify one option if another one wasn't supplied.
Though this mostly works with boolean options, it *should* work with value-based
options if you haven't supplied a default (and if you *did* supply a default,
why would you need to force the user to supply another option anyway?). Here's
a nonsense example of toggle options where one must be specified if the other is
not:

```php
$options = Optionally::options()
    ->option('on')
        ->boolean()
        ->requiredIfNull('off')
    ->option('off')
        ->boolean()
        ->requiredIfNull('on')
    ->argv()
    ;
```

Optionally handles this particular case by throwing an exception if only one of
the two options was specified.

This leads us to...

## Really, Really Advanced Options: Error Handling!

Errors in Optionally are handled by throwing exceptions. Generally speaking,
Optionally tries its best not to throw an exception unless it's absolutely
necessary, but there are some circumstances under which the underlying
GetOpt library may genuinely insist on chucking one out there.

Optionally may throw an exception if you've told it to expect a `->value()` but
the user didn't specify one, if you've used `->requiredIfNull()` or if you're
using `test` to filter out invalid values. Conveniently, Optionally will also
throw an exception if you've forgotten to append `->argv()` to your options
list, reminding you that it doesn't know you're not finished!

Handling exceptions is easy, and once the help generator is finished, it'll be
even easier:

```php
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
After all, that's why we call them *command line options* because they're
optional. Get it? Options are *optional*? Don't use this feature. Honestly, not
even a little bit. Seriously, just scroll passed this section and pretend it
doesn't exist.

Of course, if you're the masochistic type and only intend to write a script
for your use and your use only (that never happens, believe me; if it's useful,
it'll eventually leak out, and you'll send it to someone), you can make sure
that an option *absolutely must be supplied*:

```php
$options = Optionally::options()
  ->option('require-me')
    ->alias('r')
    ->required()    // don't do this
  ->argv()
  ;
```

This will throw an `OptionallyOptionsException`. You shouldn't be doing this
(did I repeat myself?), so I'll leave it to you to decide how to handle the
generated exception.

Hint: Use default values if you're thinking about using a required option.
They're easier to maintain and much less frustrating for your users.