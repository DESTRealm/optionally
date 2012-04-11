<?php

namespace org\destrealm\utilities\optionally;

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
    private $_options = array();

    /**
     * Arguments; this is anything that wasn't captured by an option.
     * @var array
     */
    private $_args = array();

    /**
     * Options help.
     * @var OptionallyHelp
     */
    private $_help = null;

    /**
     * Constructor.
     * @param array $options   Options processed by Console_Getopt.
     * @param array $settings  Option settings defined by Optionally::option and
     * friends.
     * @param array $optionMap Option map mapping options and their aliases to
     * the "master option" that defines the properties for those options or
     * aliases.
     */
    public function __construct ($options, $settings, $optionMap, $help)
    {
        $this->_options = $this->parseOptions($options[0], $settings, $optionMap);

        foreach ($optionMap as $option => $master) {

            // TODO: Clean all of this cruft up. It's really messy.

            // Handle requiredIfNull option. Throws an exception.
            if (!empty($settings[$master]['ifNull']) &&
                !array_key_exists($settings[$master]['ifNull'], $this->_options) &&
                !array_key_exists($option, $this->_options)) {
                throw new OptionallyOptionsException(
                    sprintf('The option %s is required if %s was not specified.',
                        $option, $settings[$master]['ifNull'])
                );
            }

            // Handle boolean false if a boolean option is missing.
            if (!array_key_exists($option, $this->_options) &&
                $settings[$master]['boolean'] === true) {
                foreach ($settings[$option]['aliases'] as $alias) {
                    $this->_options[$alias] = false;
                }
                $this->_options[$master] = false;
            }

            // Handle default values assignment if the option has an optional
            // value but none was passed.
            if (array_key_exists($option, $this->_options) &&
                $settings[$master]['ifMissing'] === null &&
                $settings[$master]['value'] &&
                $settings[$master]['optionalValue'] &&
                $settings[$master]['defaults'] !== null) {
                foreach ($settings[$master]['aliases'] as $alias) {
                    $this->_options[$alias] = $settings[$master]['defaults'];
                }
                $this->_options[$master] = $settings[$master]['defaults'];
            }

            // Handle ifMissing value assignment.
            if (!array_key_exists($option, $this->_options) &&
                $settings[$master]['ifMissing'] !== null) {
                foreach ($settings[$option]['aliases'] as $alias) {
                    $this->_options[$alias] = $settings[$master]['ifMissing'];
                }
                $this->_options[$master] = $settings[$master]['ifMissing'];
            }

            // Test required arguments.
            if (!array_key_exists($option, $this->_options) &&
                $settings[$master]['required'] === true) {
                throw new OptionallyOptionsException(
                    sprintf('Required option "%s" was not provided!', $option)
                );
            }

            // Run filter.
            if ($settings[$master]['value'] &&
                !empty($settings[$master]['filter'])) {
                $filter = call_user_func($settings[$master]['filter'],
                        $this->$option);
                if (!$filter && $settings[$master]['filterFailure'] === null) {
                    throw new OptionallyOptionsValueException(
                        sprintf('Value "%s" mismatch for option "%s".',
                            (string)$this->_options[$option], $option)
                    );
                } else if (!$filter &&
                    $settings[$master]['filterFailure'] !== null) {
                    $this->_options[$master] =
                        $settings[$master]['filterFailure'];
                }

            }

        }

        // Add the arguments to our tracker.
        $this->_args = $options[1];

        $this->_help = $help;

    } // end constructor

    /**
     * Getter override.
     * @param  string $value Property to get.
     * @return mixed Retrieves the value of the property defiend by $value.
     */
    public function __get ($value)
    {
        // Bail early if the option cache exists for this entry.
        if (array_key_exists($value, $this->_options)) {
            return $this->_options[$value];
        }

        return null;
    } // end __get ()

    /**
     * Retrieves the bare arguments as processed by Console_Getopt. These are
     * arguments that were not captured by the command line processor.
     * @return array Array containing all bare (remaining) arguments.
     */
    public function args ($offset=null)
    {
        if ($offset === null)
            return $this->_args;
        return array_key_exists($offset, $this->_args) ? $this->_args[ $offset ] : null;
    } // end args ()

    public function help ()
    {
        return $this->help;
    } // end help ()

    /**
     * Parses $options, compares the values contained within against
     * $this->options, and returns an array containing the option names as keys
     * and their checked values as values.
     * @param array $options Options to parse.
     * @param array $optionMap Map containing as keys a list of all known
     * aliases pointing to values of their parent option.
     * @return array Parsed values.
     */
    private function parseOptions ($options, $settings, $optionMap)
    {
        $values = array();

        foreach ($options as $option) {

            $opt = $option[0]; // Option name.
            $val = $option[1]; // Option value. Might be empty.

            // Filter long options.
            $opt = str_replace('--', '', $opt);

            if (array_key_exists($opt, $optionMap)) {
                $opt = $optionMap[$opt];
            }

            if (array_key_exists($opt, $settings)) {
                $attributes = $settings[ $opt ];
            }

            // Preemptively set the option to our captured value.
            $values[$opt] = $val;

            // Boolean true. False is handled elsewhere.
            if ($attributes['boolean'] === true && empty($val)) {
                $values[$opt] = true;
            }

            // Assign all aliases to the same value.
            foreach ($attributes['aliases'] as $alias) {
                $values[$alias] = $values[$opt];
            }
        }

        return $values;
    } // end parseOptions ()

} // end Options