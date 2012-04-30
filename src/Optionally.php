<?php

namespace org\destrealm\utilities\optionally;

/**
 * Optionally is an Optimist- (NodeJS) like API and getopt wrapper for PHP.
 * Although Optionally isn't a direct decendent of Optimist for reasons mostly
 * related to quirks in both PHP and its author, it does adhere to many of the
 * same principles first introduced in popular usage by Optimist for handling
 * command line arguments.
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
class Optionally
{

    /**
     * Processed arguments, minus the script name.
     * @var array
     */
    private $args = array();

    /**
     * argv array passed into our script via PHP.
     * @var array
     */
    private $argv = array();

    /**
     * Local PEAR Getopt reference.
     * @var Console_Getopt
     */
    private $getopt = null;

    /**
     * Last option. This is used to apply modifications to options via method
     * chaining.
     * @var string
     */
    private $lastOption = '';

    /**
     * Option names. This is a key-value store that contains references as
     * defined by Optionally::$optionTemplate.
     * @var array
     */
    private $options = array();

    /**
     * Option settings template.
     * This private var serves as the template for all options that are created
     * using Optionally::option() and dictates (hopefully) reasonable defaults.
     * @var array
     */
    private $optionTemplate = array(
        'aliases' => array(),   /* Aliases for a given option. */
        'required' => false,    /* Option is required. */
        'ifNull' => '',         /* Option is required if ifNull is absent. */
        'boolean' => false,     /* Option is boolean. */
        'callback' => null,     /* Option callbacks. See Optionally::callback(). */
        'filter' => null,       /* Test filter callbacks. See Optionally::test(). */
        'filterValue' => null,  /* Value to use on filter failure. */
        'defaults' => null,     /* Option default value(s). */
        'examples' => null,     /* Usage example(s). */
        'ifMissing' => null,    /* Default value if the option is missing. */
        'value' => false,       /* Value is required. */
        'optionalValue' => false,   /* Value is optional. */
        'argName' => '',        /* Argument name; used for help text. */
    );

    /**
     * Usage examples for the host script. This typically shouldn't be needed if
     * you provide example usage for each option individually.
     * @var array
     */
    private $scriptExamples = array();

    /**
     * Name of the host script. This is derived from the first offset of
     * $_SERVER['argv'] or whatever the value of $args is as passed in from the
     * constructor.
     * @var string
     */
    private $scriptName = '';

    /**
     * Usage tips for the host script. If this isn't provided, the default
     * listed here will be used instead.
     * @var string
     */
    private $usage = 'Usage: %script [options]';

    /**
     * Factory method to create a new Optioanlly instance. Useful for method
     * chaining without creating intermediate variables.
     * @return Optionally Instance ($this).
     */
    public static function options ($args=null)
    {
        $optionally = new self($args);

        return $optionally;
    } // end options ()

    /**
     * Constructor.
     * @param array $args=array() Arguments. Pass an argv-like array of
     * arguments to override what Optionally believes it's supposed to handle.
     * This is mostly useful for unit testing. This must include the script name
     * as the first argument.
     */
    public function __construct ($args=null)
    {
        if (empty($args)) {
            $args = $_SERVER['argv'];
        }

        $args = array_slice($args, 1);

        $this->args = $args;
        $this->getopt = new Console_Getopt();
        $this->help = new Help($_SERVER['argv'][0]);
    } // end constructor

    /**
     * Raises an error when a non-existent property is called. Since such
     * properties are usually requested when the user forgest to call
     * Optionally::argv(), this should give them a hint regarding their mistake.
     * @param  string $value Requested property.
     * @throws OptionallyException If Non-existent property is requested.
     */
    public function __get ($value)
    {
        if (!method_exists($this, $value) && !property_exists($this, $value)) {
            throw new MissingArgvException(
                sprintf('Invalid option "%s." Did you forget to call argv()?',
                    $value)
            );
        }
    } // end __get ()

    /**
     * Attach an alias to the current option.
     * @param  string $alias Option alias.
     * @return Optionally Instance ($this).
     */
    public function alias ($alias)
    {
        $option =& $this->getLastOption();
        $option['aliases'][] = $alias;

        $mangled = $this->mangle($alias);
        if (!empty($mangled)) {
            $option['aliases'] = array_merge(
                $option['aliases'],
                $mangled
            );
        }

        $this->fireCallback('alias');

        return $this;
    } // end alias ()

    /**
     * Returns a usable but immutable Options object
     * @return Options
     */
    public function argv ()
    {
        $this->help->setOptions($this->options);
        $this->help->setUsage($this->usage);
        $shortOpts = '';
        $longOpts = array();
        $optionMap = array();

        $this->fireCallback('pre');

        /**
         * Appends the appropriate suffix to either $shortOpts or $longOpts.
         */
        $appendOpts = function ($option, $suffix) use (&$shortOpts, &$longOpts) {

            if (strlen($option) === 1) {
                $shortOpts .= $option.$suffix;
            } else {
                $longOpts[] = $option.$suffix;
            }

        };

        // Figure out which options have values or optional values. Also update
        // their aliases.
        foreach ($this->options as $option => $prefs) {

            $appendOpts($option,
                $this->getSuffixForOption(
                    $option,
                    $prefs['value'],
                    $prefs['optionalValue']
                )
            );

            $optionMap[$option] = $option;

            /*if (!empty($prefs['examples'])) {
                $help->addExamples($option, $prefs['examples'],
                    $prefs['aliases']);
            }*/

            if (!empty($prefs['aliases'])) {
                foreach ($prefs['aliases'] as $alias) {

                    $optionMap[$alias] = $option;

                    $appendOpts($alias,
                        $this->getSuffixForOption(
                            $alias,
                            $prefs['value'],
                            $prefs['optionalValue']
                        )
                    );

                }
            }

        }

        $options = $this->getopt->getopt2(
            $this->args,
            $shortOpts,
            $longOpts,
            true
        );

        $this->fireCallback('post');

        return new Options($options, $this->options, $optionMap, $this->help);
    } // end argv ()

    /**
     * Instructs Optionally that the current option should be considered a
     * boolean option. The option will appear as a property to the current
     * Optionally instance even if it wasn't specified on the command line;
     * however, if the option wasn't provided by the user, its value will be
     * false--otherwise true.
     * @return Optionally Instance ($this).
     */
    public function boolean ()
    {
        $option =& $this->getLastOption();
        $option['boolean'] = true;

        // Boolean options cannot be required options.
        $option['required'] = false;

        $this->fireCallback('boolean');

        return $this;
    } // end boolean ()

    /**
     * Callback function called prior to handling an option, after an option
     * has been handled, or for each call during an option's method chain calls.
     * The callback must accept three arguments: The option name, the option
     * handling stage, and a reference to this class. Valid handling stages are
     * "pre", "post", or any name matching methods in this class that handles
     * option attributes; this includes "alias", "describe", "required", and so
     * forth. Obviously, callback() must be configured prior to calling any of
     * the affected methods.
     * @param  function $callback [description]
     * @return Optionally Instance ($this).
     */
    public function callback ($callback)
    {
        $option =& $this->getLastOption();

        if (empty($option['callback'])) {
            $option['callback'] = $callback;
        } else {
            if ((array)$option['callback'] !== $callback) {
                $option['callback'] = array($option['callback']);
            }
            $option['callback'][] = $callback;
        }

        return $this;
    } // end callback ()

    /**
     * Sets a default value (or values) for the current option.
     * @param  mixed $value Option default value.
     * @return Optionally Instance ($this).
     */
    public function defaults ($value)
    {
        $option =& $this->getLastOption();
        $option['defaults'] = $value;

        $this->fireCallback('defaults');

        return $this;
    } // end defaults ()


    /**
     * Default value if the option is missing.
     * @param  [type] $value [description]
     * @return [type]
     */
    public function defaultsIfMissing ($value)
    {
        $option =& $this->getLastOption();
        $option['ifMissing'] = $value;

        $this->fireCallback('defaultsIfMissing');

        return $this;
    } // end defaultsIfMissing ()

    /**
     * Describes an option. This will be displayed alongside the option and
     * each of its aliases when Optionally::help() is called.
     * @param  string $help Help string.
     * @return Optionally Instance ($this).
     */
    public function describe ($help, $argName='')
    {
        $option =& $this->getLastOption();
        $this->help->addDescription($this->lastOption, $help, $argName);

        $this->fireCallback('describe');

        return $this;
    } // end help ()

    /**
     * Includes example usage for an option. While this isn't required,
     * examples() is a useful addendum to describe() and will be displayed after
     * the describe() output. Be aware that examples() will not function if
     * describe() wasn't first called.
     *
     * $examples may be a string or an array; if it's a string, Optionally will
     * assume that only a single example is listed. If $examples is an array,
     * Optionally will treat each element as its own individual example.
     * @param  mixed $examples String (single example) or an array (multiple
     * examples).
     * @return Optionally Instance ($this).
     */
    public function examples ($examples)
    {
        $option =& $this->getLastOption();
        $option['examples'] = $examples;

        $this->fireCallback('examples');

        return $this;
    } // end examples ()

    /**
     * Creates an option. Options may not include a leading dash (-) or trailing
     * getopt suffixes (":" or "=") and must be comprised of a single character
     * or string.
     *
     * If $settings is provided, option() can be used in a somewhat more
     * traditional manner without method chaining and may be useful for certain
     * programmatic applications. All options are supported in $settings. Any
     * options missing from $settings will be merged with $this->optionTemplate
     * to ensure reasonable defaults are maintained.
     * @param  string $option           Option.
     * @param  array  $settings=array() Settings override. See
     * $this->optionTemplate for usage examples.
     * @return Optionally Instance ($this).
     */
    public function option ($option, $settings=array())
    {
        $this->lastOption = $option;

        if (!array_key_exists($option, $this->options)) {
            $this->options[ $option ] = $this->optionTemplate;
        }

        if (!empty($settings)) {
            $this->options[ $option ] = array_merge($this->optionTemplate, $settings);
        }

        $mangled = $this->mangle($option);
        if (!empty($mangled)) {
            $this->options[$option]['aliases'] = array_merge(
                $this->options[$option]['aliases'],
                $mangled
            );
        }

        return $this;
    } // end option ()

    /**
     * Instructs Optionally to treat the specified option as possessing an
     * optional argument.
     * @return [type]
     */
    public function optional ()
    {
        $option =& $this->getLastOption();
        $option['optionalValue'] = true;

        $this->fireCallback('optional');

        return $this;
    } // end optional ()

    /**
     * Indicates that the option is required. Beware when using this feature;
     * command line options are called "options" for a reason! :) You should
     * elect to provide sensible defaults whenever options are missing rather
     * than forcing end users to supply them on the command line.
     * @return Optionally Instance ($this).
     */
    public function required ()
    {
        $option =& $this->getLastOption();
        $option['required'] = true;

        // Required options cannot be boolean options.
        $option['boolean'] = false;

        $this->fireCallback('required');

        return $this;
    } // end required ()

    /**
     * Indicates that the current option is required if (and only if) $name
     * was not provided. This can be useful for providing toggle options.
     * @param  string $name Option.
     * @return Optionally Instance ($this).
     */
    public function requiredIfNull ($name)
    {
        $name = (string)$name;
        $option =& $this->getLastOption();
        $option['ifNull'] = $name;

        $this->fireCallback('requiredIfNull');

        return $this;
    } // end requiredIfNull ()

    /**
     * Tests the option's value to determine if it is acceptable. $callback
     * must be a function that accepts a single argument and returns a boolean
     * value. The argument passed to $callback will be the value of the current
     * option.
     *
     * If the option passed in was declared as a boolean (exists or not), the
     * values true or false will be passed into $callback depending on whether
     * or not the option was provided by the client code.
     * @param  function $callback Callback to process option.
     * @return Optionally Instance ($this).
     */
    public function test ($callback, $default=null)
    {
        $option =& $this->getLastOption();
        $option['filter'] = $callback;
        $option['filterFailure'] = $default;

        return $this;
    } // end test ()

    /**
     * Adds usage text for the script itself, e.g.:
     *
     * ->usage('%script usage: ./%script [options] <file_to_load>
     *
     * Will generate at the top of the help output (assuming your script is
     * named example-script.php):
     *
     * example-script.php usage: ./example-script.php [options] <file_to_load>
     * @param  string $usage Usage text.
     * @return Optionally Instance ($this).
     */
    public function usage ($usage)
    {
        $this->help->setUsage($usage);

        return $this;
    } // end usage ()


    /**
     * Indicates that an option must possess a value argument.
     * @return Optionally Instance ($this).
     */
    public function value ($value=null)
    {
        $option =& $this->getLastOption();
        $option['value'] = true;

        if ($value !== null) {
            $option['defaults'] = $value;
            $option['ifMissing'] = $value;
        }

        $this->fireCallback('value');

        return $this;
    } // end value ()

    /**
     * Fires a callback for the callback stage $stage.
     * @param  string $stage Callback stage.
     */
    private function fireCallback ($stage)
    {
        $option =& $this->getLastOption();
        $self =& $this;

        if (!empty($option['callback'])) {
            if ((array)$option['callback'] !== $option['callback']) {
                call_user_func($option['callback'], $this->lastOption, $stage, $this);
            } else {
                array_map(
                    function ($callback) use (&$self, $stage) {
                        call_user_func($callback, $self->lastOption, $stage, $self);
                    }, $option['callback']);
            }
        }
    } // end fireCallback ()

    /**
     * Retrieves a reference to the last option defined by option(). This is
     * used exclusively by method chaining in order to determine which modifier
     * should be applied to what option.
     * @return object Reference to the last option offset in $this->options.
     */
    private function &getLastOption ()
    {
        return $this->options[ $this->lastOption ];
    } // end getLastOption ()

    /**
     * Retrieves a Getopt suffix for the option $option. Single letter options
     * will return a suffix of ":" while word options will return a suffix of
     * "=". Suffixes are returned only if $hasValue is true; double-suffixes
     * will be returned if both $hasValue is true and $optional is true.
     * @param  string  $option         Option.
     * @param  boolean $hasValue=false Option requests a value.
     * @param  boolean $optional=false Option requests an optional value.
     * @return string Empty string, ":", "::", "=", or "==".
     */
    private function getSuffixForOption ($option, $hasValue=false, $optional=false)
    {
        $suffix = '';

        if (strlen($option) === 1) {

            // Option requires a value.
            if ($hasValue) {
                $suffix = ':';
            }

            // Option value is optional. ->optional() has no effect unless
            // ->value() is also specified.
            if ($hasValue && $optional) {
                $suffix = '::';
            }

        } else {

            if ($hasValue) {
                $suffix = '=';
            }

            if ($hasValue && $optional) {
                $suffix = '==';
            }

        }

        return $suffix;
    } // end getSuffixForOption ()

    /**
     * Mangles an option name so that it's safe to use as a PHP object property.
     * @param  string $option Option.
     * @return string Mangled option.
     */
    private function mangle ($option)
    {
        $aliases = array();
        if (strpos($option, '-') !== false) {
            $parts = explode('-', $option);
            $aliases[] = implode('_', $parts);

            $first = array_shift($parts);
            $rest = array_map('ucfirst', $parts);
            $aliases[] = implode('', array_merge((array)$first, $rest));
        }

        return $aliases;
    } // end mangle ()

} // end Optionally