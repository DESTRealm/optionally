<?php

namespace org\destrealm\utilities\optionally;

/**
 * Options container class.
 *
 * This class serves as a container for processed options. When user code calls
 * Optionally::argv(), an instance of this class will be returned containing
 * all necessary logic and properties to handle configured command line options.
 */
class Options
{

    private $_options = array();

    private $_args = array();

    /**
     * Constructor.
     * @param array $options   Options processed by Console_Getopt.
     * @param array $settings  Option settings defined by Optionally::option and
     * friends.
     * @param array $optionMap Option map mapping options and their aliases to
     * the "master option" that defines the properties for those options or
     * aliases.
     */
    public function __construct ($options, $settings, $optionMap)
    {
        $this->_options = $this->parseOptions($options[0], $settings, $optionMap);

        // Handle boolean false options.
        foreach ($optionMap as $option => $master) {
            if (!array_key_exists($option, $this->_options) &&
                $settings[$master]['boolean'] === true) {
                foreach ($settings[$option]['aliases'] as $alias) {
                    $this->_options[$alias] = false;
                }
                $this->_options[$master] = false;
            }
        }

        // Add the arguments to our tracker.
        $this->_args = $options[1];

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

        if (array_key_exists($value, $this->_options)) {
            return $this->_options[$value];
        }

        if (!property_exists($this, $value)) {
            return null;
        }
    } // end __get ()

    /**
     * Retrieves the bare arguments as processed by Console_Getopt. These are
     * arguments that were not captured by the command line processor.
     * @return array Array containing all bare (remaining) arguments.
     */
    public function args () { return $this->_args; }

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

            // Preemptively set the option to our captured value.
            $values[$opt] = $val;

            $attributes = null;

            // Find which option we're honoring; this is one of the master
            // $this->options key-value store or something in the $optionMap
            // which will lead us there.
            if (array_key_exists($opt, $settings)) {
                $attributes = $settings[ $opt ];
            } else if (in_array($opt, $optionMap)) {
                $attributes = $settings[ $optionMap[$opt] ];
            } else {
                continue;
            }

            // If the value is empty and we're expecting a value, and this value
            // is *not* optional, throw an error.
            if ($attributes['value'] && !$attributes['optionalValue'] &&
                empty($val)) {
                throw new OptionallyParserException(
                    'Value is required for option.',
                    $opt,
                    OptionallyException::REQUIRES_ARGUMENT
                );
            }

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