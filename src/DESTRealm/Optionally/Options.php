<?php

namespace DESTRealm\Optionally;

use DESTRealm\Optionally\Exceptions\OptionsException;
use DESTRealm\Optionally\Exceptions\OptionsValueException;

/**
 * Options container class.
 *
 * This class serves as a container for processed options. When user code calls
 * Optionally::argv(), an instance of this class will be returned containing
 * all necessary logic and properties to handle configured command line options.
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
class Options
{

    /**
     * Parsed options.
     * @var array
     */
    private $options = array();

    /**
     * Arguments; this is anything that wasn't captured by an option.
     * @var array
     */
    private $args = array();

    /**
     * Options help.
     * @var Help
     */
    private $help = null;

    /**
     * Constructor.
     * @param array $options   Options processed by Getopt.
     * @param array $settings  Option settings defined by Optionally::option and
     * friends.
     * @param array $optionMap Option map mapping options and their aliases to
     * the "master option" that defines the properties for those options or
     * aliases.
     * @param OptionallyHelp $help Help instance.
     */
    public function __construct ($options, $args, $help)
    {
        $this->options  = $options;
        $this->args     = $args;
        $this->help     = $help;
    } // end constructor

    /**
     * Getter override.
     * @param  string $option Property to get.
     * @return mixed Retrieves the value of the property defiend by $value.
     */
    public function __get ($option)
    {
        return $this->getOption($option);
    } // end __get ()

    /**
     * Retrieves the bare arguments as processed by Console_Getopt. These are
     * arguments that were not captured by the command line processor.
     * @return array Array containing all bare (remaining) arguments.
     */
    public function args ($offset=null)
    {
        if ($offset === null)
            return $this->args;
        return array_key_exists($offset, $this->args) ? $this->args[ $offset ] : null;
    } // end args ()

    /**
     * Returns an option $option if it was provided on the command line or
     * NULL if the option was not specified. Note that certain options might
     * have default values and may return those defaults instead of null.
     * @param  string $option Option whose value should be fetched and returned.
     * @return mixed Returns the option value if set; null otherwise.
     */
    public function getOption ($option)
    {
        if (array_key_exists($option, $this->options)) {

            return $this->options[$option];

        } else if (array_key_exists($option, $this->optionMap) &&
            array_key_exists($this->optionMap[$option], $this->options)) {

            return $this->options[ $this->optionMap[$option] ];

        }

        return null;
    } // end getOption ()

    /**
     * Generate help text.
     * @return string Returns the generated help text for all defined options.
     */
    public function help ()
    {
        return $this->help->help();
    } // end help ()

} // end Options