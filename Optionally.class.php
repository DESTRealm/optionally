<?php

namespace org\destrealm\utilities;

$__path = dirname(__FILE__).DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.
    'Getopt.php';

if (file_exists($__path)) {
    require_once $__path;
} else {
    require_once 'Getopt.php';
}

/**
 * Optionally is an Optimist- (NodeJS) like API and getopt wrapper for PHP.
 * Although Optionally isn't a direct decendent of Optimist for reasons mostly
 * related to quirks in both PHP and its author, it does adhere to many of the
 * same principles first introduced in popular usage by Optimist for handling
 * command line arguments.
 */
class Optionally
{

    /**
     * Array of arguments passed in addition to our options.
     * @var array
     */
    private $_args = array();

    /**
     * argv array passed into our script via PHP.
     * @var array
     */
    private $argv = array();

    private $getopt = null;

    /**
     * Last option. This is used to apply modifications to options via method
     * chaining.
     * @var string
     */
    private $lastOption = '';

    /**
     * Option cache.
     * @var array
     */
    private $optionCache = array();

    /**
     * Option names. This is a key-value store that contains references as
     * defined by Optionally::$optionTemplate.
     * @var array
     */
    private $options = array();

    private $optionTemplate = array(
        'aliases' => array(),   /* Aliases for a given option. */
        'description' => null,  /* Option description. Shown by help(). */
        'required' => false,    /* Option is required. */
        'ifNull' => '',         /* Option is required if ifNull is absent. */
        'boolean' => false,     /* Option is boolean. */
        'callback' => null,     /* Option callbacks. See Optionally::callback(). */
        'defaults' => null,     /* Option default value(s). */
        'examples' => null,     /* Usage example(s). */
        'value' => false,       /* Value is required. */
        'optionalValue' => false,   /* Value is optional. */
    );

    /**
     * Factory method to create a new Optioanlly instance. Useful for method
     * chaining without creating intermediate variables.
     * @return [type]
     */
    public static function factory ($args=array())
    {
        $optionally = new self($args);

        return $optionally;
    } // end factory ()

    /**
     * Constructor.
     * @param array $args=array() Arguments. Pass an argv-like array of
     * arguments to override what Optionally believes it's supposed to handle.
     * This is mostly useful for unit testing.
     */
    public function __construct ($args=array())
    {
        if (empty($args)) {
            $args = $_SERVER['argv'];
        }

        $this->args = $args;
        $this->getopt = new Console_Getopt();
    } // end constructor

    /**
     * Attach an alias to the current option.
     * @param  string $alias Option alias.
     * @return Optionally Instance ($this).
     */
    public function alias ($alias)
    {
        $option =& $this->getLastOption();
        $option['aliases'][] = $alias;

        return $this;
    } // end alias ()

    /**
     * [argc description]
     * @return [type]
     */
    public function argc ()
    {

    } // end argc ()

    /**
     * Returns the non-option arguments left over from getopt.
     * @return array Arguments.
     */
    public function args ()
    {
        $this->prepareOptionCache();
        return $this->_args;
    } // end args ()

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

        return $this;
    } // end boolean ()

    /**
     * Callback function called prior to handling an option, after an option
     * has been handled, or for each call during an option's method chain calls.
     * The callback must accept two arguments with one optional: The option
     * name, the option handling stage, and a reference to this class. Valid
     * handling stages are "pre", "post", or any name matching methods in this
     * class that handler option attributes; this includes "alias",
     * "describe", "required", and so forth.
     * @param  function $callback [description]
     * @return [type]
     */
    public function callback ($callback)
    {

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

        return $this;
    } // end default ()

    /**
     * Describes an option. This will be displayed alongside the option and
     * each of its aliases when Optionally::help() is called.
     * @param  string $help Help string.
     * @return Optionally Instance ($this).
     */
    public function describe ($help)
    {
        $option =& $this->getLastOption();
        $option['description'] = $help;

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

        return $this;
    } // end examples ()

    /**
     * Displays derived help text pulled in from describe().
     * @return string Help text.
     */
    public function help ()
    {

    } // end help ()

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

        return $this;
    } // end required ()

    /**
     * Indicates that the current option is required if (and only if) $option
     * was not provided. This can be useful for providing toggle options.
     * @param  string $option Option.
     * @return Optionally Instance ($this).
     */
    public function requiredIfNull ($option)
    {
        $option =& $this->getLastOption();
        $option['ifNull'] = $option;

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
     * @return [type]
     */
    public function test ($callback)
    {

    } // end test ()

    /**
     * Indicates that an option must possess a value argument.
     * @return Optionally Instance ($this).
     */
    public function value ()
    {
        $option =& $this->getLastOption();
        $option['value'] = true;

        return $this;
    } // end value ()

    /**
     * Retrieves an option value from this Optionally instance. Be aware that
     * this functionality may be shifted into a subordinate class in the future.
     * __get() is a PHP override method for retrieving values from a class
     * instance, e.g. $optionally->someValue.
     *
     * Calling any property on an Optionally instance that does not exist will
     * trigger a preparation of the option cache.
     * @param  string $value Property value; more specifically (or hopefully),
     * the value of a command line option.
     * @return mixed Returns the value of the option if it exists or null
     * otherwise. This might also generate errors in rare circumstances where
     * an option conflicts with an already defined property. Use with care.
     */
    public function __get ($value)
    {
        // Bail early if the option cache exists for this entry.
        if (array_key_exists($value, $this->optionCache)) {
            return $this->optionCache[$value];
        }

        $this->prepareOptionCache();

        if (array_key_exists($value, $this->optionCache)) {
            return $this->optionCache[$value];
        }

        if (!property_exists($this, $value)) {
            return null;
        }
    } // end getter

    /**
     * Retrieves a reference to the last option defined by option(). This is
     * used exclusively by method chaining in order to determine which modifier
     * should be applied to what option.
     * @return object Reference to the last option offset in $this->options.
     */
    private function &getLastOption ()
    {
        $this->optionCache = array();
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

    /**
     * Mangles all aliases for the option $option, including the option's name.
     * @param  string $option Option to mangle.
     */
    private function mangleAll ($option)
    {
        $mangled = array();
        if (!empty($this->options[$option]['aliases'])) {
            foreach ($this->options[$option]['aliases'] as $alias) {
                $mangled = array_merge($this->mangle($alias), $mangled);
            }

            $mangled = array_merge($this->mangle($option), $mangled);
        }

        $this->options[$option]['aliases'] = array_merge(
            $this->options[$option]['aliases'],
            $this->mangle($option)
        );
    } // end mangleAll ()

    /**
     * Parses $options, compares the values contained within against
     * $this->options, and returns an array containing the option names as keys
     * and their checked values as values.
     * @param array $options Options to parse.
     * @param array $optionMap Map containing as keys a list of all known
     * aliases pointing to values of their parent option.
     * @return array Parsed values.
     */
    private function parseOptions ($options, $optionMap)
    {
        $values = array();

        foreach ($options as $option) {

            $opt = $option[0]; // Option name.
            $val = $option[1]; // Option value. Might be empty.

            // Filter long options.
            $opt = str_replace('--', '', $opt);

            // Preemptively set the option to our captured value.
            $values[$opt] = $val;

            $settings = null;

            // Find which option we're honoring; this is one of the master
            // $this->options key-value store or something in the $optionMap
            // which will lead us there.
            if (array_key_exists($opt, $this->options)) {
                $settings = $this->options[ $opt ];
            } else if (in_array($opt, $optionMap)) {
                $settings = $this->options[ $optionMap[$opt] ];
            } else {
                continue;
            }

            // If the value is empty and we're expecting a value, and this value
            // is *not* optional, throw an error.
            if ($settings['value'] && !$settings['optionalValue'] &&
                empty($val)) {
                throw new OptionallyParserException(
                    'Value is required for option.',
                    $opt,
                    OptionallyException::REQUIRES_ARGUMENT
                );
            }

            // Boolean true. False is handled elsewhere.
            if ($settings['boolean'] === true && empty($val)) {
                $values[$opt] = true;
            }

            // Assign all aliases to the same value.
            foreach ($settings['aliases'] as $alias) {
                $values[$alias] = $values[$opt];
            }
        }

        return $values;
    } // end parseOptions ()

    /**
     * Prepares the option cache which is used to extract options for user code
     * that expects their values to be present as getter properties.
     */
    private function prepareOptionCache ()
    {
        $shortOpts = '';
        $longOpts = array();

        $optionMap = array();

        if (!empty($this->optionCache)) {
            return;
        }

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

        foreach ($this->options as $option => $prefs) {

            // Mangles option names.
            $this->mangleAll($option);

            $appendOpts($option,
                $this->getSuffixForOption(
                    $option,
                    $prefs['value'],
                    $prefs['optionalValue']
                )
            );

            $optionMap[$option] = $option;

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
            $longOpts
        );

        // Parse the options we were given.
        $this->optionCache = $this->parseOptions($options[0], $optionMap);

        // Handle boolean false options.
        foreach ($optionMap as $option => $master) {
            if (!array_key_exists($option, $this->optionCache) &&
                $this->options[$master]['boolean'] === true) {
                foreach ($this->options[$option]['aliases'] as $alias) {
                    $this->optionCache[$alias] = false;
                }
                $this->optionCache[$master] = false;
            }
        }

        // Add the arguments to our tracker.
        $this->_args = $options[1];
    } // end prepareOptionCache ()

} // end Optionally

class OptionallyOption extends Optionally
{
    private $optionally;

    public function __construct ($optionally)
    {
        $this->optionally = $optionally;
    } // end constructor

    public function boolean ($args)
    {

    } // end boolean ()

} // end OptionallyOption