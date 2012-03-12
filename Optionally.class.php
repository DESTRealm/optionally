<?php

namespace org\destrealm\utilities;

use Console_Getopt;

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

    private $optsShort = '';

    private $optsLong = array();

    private $parsedOptions = array();

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

    public function args ($name)
    {

    } // end args ()

    public function argv ($offset)
    {

    } // end argv ()

    public function boolean ($args)
    {
        $option =& $this->getLastOption();
        $option['boolean'] = true;

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
        if (strlen(str_replace(':', '', $option)) === 1) {
            $this->optsShort .= $option;
        } else if (strlen($option) > 1) {
            $this->optsLong[] = $option;
        }

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
        $option['uaage'] = $usage;

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

        $this->parsedOptions = $this->getopt->getopt2(
            $this->args,
            $this->optsShort,
            $this->optsLong
        );

        if (array_key_exists($value, $this->options)) {
            $option = $this->options[$value];

        }

        if (!property_exists($this, $value)) {
            return null;
        }

        return $this->$value;
    } // end getter

    private function &getLastOption ()
    {
        // Clear the option cache since accessing this method typically means
        // that the option data is being changed.

        if (array_key_exists($this->lastOption, $this->optionCache)) {
            unset($this->optionCache[$this->lastOption]);
        }

        return $this->options[ $this->lastOption ];
    } // end getLastOption ()

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