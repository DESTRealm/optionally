<?php

namespace DESTRealm\Optionally;

use DESTRealm\Optionally\Getopt\Getopt;

/**
 * Option builder.
 *
 * This class generates option data used by the Options object.
 */
class OptionBuilder
{
    private $args = array();

    private $getopt = null;

    private $help = array();

    private $options = array();

    private $optionMap = array();


    public static function buildOptions ($args, $options, $map, $help)
    {
        $builder = new self($args, $options, $map);
        $options = $builder->build();
    } // end buildOptions ()

    private function __construct ($args, $options, $map)
    {
        $this->args = $args;
        $this->getopt = new Getopt();
        //$this->help = $help;
        $this->options = $options;
        $this->optionMap = $map;
    } // end constructor

    public function build ()
    {
        $getopt = $this->parseOptions();

        $args = $getopt[1];
        $options = $getopt[0];

        $parsedOptions = $this->assignValues(
            $options,
            $this->options
        );

        print_r($parsedOptions);

        //$evaluatedOptions = $this->evaluateOptions($parsedOptions);

    } // end build ()

    private function assignValues ($options, $settings)
    {
        $values = array();

        foreach ($options as $option) {

            $opt = $option[0]; // Option name.
            $val = $option[1]; // Option value. Might be empty.

            // Filter long options.
            $opt = str_replace('--', '', $opt);

            if (!array_key_exists($opt, $values)) {
                $values[$opt] = array(
                    'count' => 1,
                    'value' => array($val),
                );
            } else {
                $values[$opt]['count'] += 1;
                $values[$opt]['value'][] = $val;
            }

            continue;

            if (array_key_exists($opt, $this->optionMap)) {
                $opt = $this->optionMap[$opt];
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
    } // end assignValues ()

    private function evaluateOptions ($options)
    {
        $evaluated = array();

        foreach ($this->optionMap as $option => $master) {

            //if (array_key_exists($

        }
    } // end evaluateOptions ()

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

    private function parseOptions ()
    {
        $shortOpts = '';
        $longOpts = array();

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

            if (!empty($prefs['aliases'])) {
                foreach ($prefs['aliases'] as $alias) {

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

        return $this->getopt->getopt2(
            $this->args,
            $shortOpts,
            $longOpts,
            true
        );
    } // end parseArgs ()
} // end OptionBuilder