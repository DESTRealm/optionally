<?php

namespace org\destrealm\utilities;

$__path = dirname(__FILE__).DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.
    'Getopt.php';

if (file_exists($__path)) {
    require_once $__path;
} else {
    require_once 'Getopt.php';
}

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
        'boolean' => false,     /* Option is boolean. */
        'callback' => null,     /* Option callbacks. See Optionally::callback(). */
        'defaults' => null,     /* Option default value(s). */
        'usage' => null,        /* Example usage. */
        'value' => false,       /* Value is required. */
        'optionalValue' => false,   /* Value is optional. */
    );

    private $parsedOptions = array();

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

    public function __construct ($args=array())
    {
        if (empty($args)) {
            $args = $_SERVER['argv'];
        }

        $this->args = $args;
        $this->getopt = new Console_Getopt();
    } // end constructor

    public function alias ($alias)
    {
        $option =& $this->getLastOption();
        $option['aliases'][] = $alias;

        return $this;
    } // end alias ()

    public function argc ()
    {

    } // end argc ()

    public function args ()
    {
        $this->prepareOptionCache();
        return $this->_args;
    } // end args ()

    public function argv ($offset)
    {

    } // end argv ()

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

    public function defaults ($value)
    {
        $option =& $this->getLastOption();
        $option['defaults'] = $value;

        return $this;
    } // end default ()

    public function describe ($help)
    {
        $option =& $this->getLastOption();
        $option['description'] = $help;

        return $this;
    } // end help ()

    public function help ()
    {

    } // end help ()

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

    public function required ()
    {
        $option =& $this->getLastOption();
        $option['required'] = true;

        // Required options cannot be boolean options.
        $option['boolean'] = false;

        return $this;
    } // end required ()

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

    public function usage ($usage)
    {
        $option =& $this->getLastOption();
        $option['usage'] = $usage;

        return $this;
    } // end usage ()

    /**
     * Indicates that an option must possess an argument for its value.
     * @return [type]
     */
    public function value ()
    {
        $option =& $this->getLastOption();
        $option['value'] = true;

        return $this;
    } // end value ()

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
     * @param  [type] $options [description]
     * @return [type]
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