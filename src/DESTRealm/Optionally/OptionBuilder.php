<?php

namespace DESTRealm\Optionally;

use DESTRealm\Optionally\Getopt\Getopt;
use DESTRealm\Optionally\Options;
use DESTRealm\Optionally\Exceptions\OptionsException;
use DESTRealm\Optionally\Exceptions\OptionsValueException;

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

        return new Options($options, $args, $help);
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

        return $this->buildAliases($this->evaluateOptions($parsedOptions));

    } // end build ()

    /**
     * Accepts the output from Getopt and assigns values accordingly. Values
     * are rearranged according to their master option name (no aliases will
     * appear in the returned value), how many times they've appeared, and their
     * value.
     * @param array $options Getopt options.
     * @return array Returns an array containing the following:
     *
     * array(
     *   'option_name' => array(
     *     'count' => number_of_appearances,
     *     'value' => array(val1, val2, ..., valn+1) OR null
     *   )
     * )
     *
     * If the option isn't a value option or had no value specified, the "value"
     * offset will be null, even if further instances of the same option are
     * provided. The "value" offset is appended to in order of the options'
     * appearance.
     */
    private function assignValues ($options)
    {
        $values = array();

        foreach ($options as $option) {

            $opt = $option[0]; // Option name.
            $val = $option[1]; // Option value. Might be empty.

            // Filter long options.
            $opt = str_replace('--', '', $opt);

            // Force all options to their master name.
            $opt = $this->optionMap[$opt];

            if (!array_key_exists($opt, $values)) {

                if ($val !== null) {
                    $val = array($val);
                }

                $values[$opt] = array(
                    'count' => 1,
                    'value' => $val,
                );

            } else {

                $values[$opt]['count'] += 1;

                // Empy current and former values imply we've nothing to do.
                if ($val === null && $values[$opt]['value'] === null) {
                    continue;
                }

                // We've snagged a value of some sort. If the previous one was
                // null, we convert it to an array and slap the current value
                // in there.
                if ($val !== null && $values[$opt]['value'] === null) {
                    $values[$opt]['value'] = array($val);
                } else {
                    $values[$opt]['value'][] = $val;
                }

            }

        }

        return $values;
    } // end assignValues ()

    /**
     * Builds aliases for the evaluated values $values.
     * @param  array $values Evaluated values.
     * @return array         Evaluated values plus aliases.
     */
    private function buildAliases ($values)
    {
        foreach ($this->optionMap as $option => $master) {

            $aliases = array_merge(
                $this->mangle($master),
                $this->mangle($option)
            );

            foreach ($aliases as $alias) {

                // Don't overwrite aliases that already exist. If they do,
                // there's likely a name collision somewhere and we should bail
                // out.
                if (array_key_exists($alias, $values)) {
                    continue;
                }

                $values[$alias] =& $values[$master];

            }

        }

        return $values;
    } // end buildAliases ()

    private function evaluateOptions ($parsed)
    {
        $evaluated = array();

        foreach ($this->options as $option => $defaults) {

            // Handle ifNull. This first checks to verify that the option is
            // mappable.
            if (!empty($defaults['ifNull']) &&
                array_key_exists($defaults['ifNull'], $this->optionMap)) {
                $required = $this->optionMap[ $defaults['ifNull'] ];
                if (!array_key_exists($required, $parsed)) {
                    throw new OptionsException(
                        sprintf(
                            'The option %s is required if %s was not specified.',
                            $option, $settings[$master]['ifNull']
                        )
                    );
                }
            }

            // Default value assignments. If the default value is specified,
            // all options are assigned this at first, and then overwritten if
            // the option was provided.
            if ($defaults['defaults'] !== null) {
                $evaluated[$option] = $defaults['defaults'];
            }

            // Value assignments; first, values are assigned if the option was
            // specified, and second if the options were not.
            if (array_key_exists($option, $parsed)) {

                $value = $parsed[$option]['value'];

                // Boolean values. This will immediately bail on match.
                if ($defaults['boolean']) {
                    $evaluated[$option] = true;
                    continue;
                }

                // Value options; optional and required.
                if ((array)$value === $value) {
                    $evaluated[$option] = $value[count($value)-1];
                } else {
                    $evaluated[$option] = $value;
                }

                // Countable options.
                if ($defaults['isCountable']) {
                    $evaluated[$option] = $parsed[$option]['count'];
                }

                // Array options.
                if ($defaults['isArray']) {
                    $evaluated[$option] = (array)$value;
                }

            } else {

                // Handle required options.
                if ($defaults['required']) {
                    throw new OptionsException(
                        sprintf(
                            'Required option "%s" was not provided!"',
                            $option
                        )
                    );
                }

                // Boolean values. This will immediately bail on match.
                if ($defaults['boolean']) {
                    $evaluated[$option] = false;
                    continue;
                }

                // Default value assignment, if the option is missing. This
                // requires using defaultsIfMissing().
                if ($defaults['ifMissing'] !== null) {
                    $evaluated[$option] = $defaults['ifMissing'];
                }

                // Countable options.
                if ($defaults['isCountable']) {
                    $evaluated[$option] = 0;
                }

                // Array options.
                if ($defaults['isArray']) {
                    $evaluated[$option] = array();
                }
            }


            // Handle filters.
            if ($defaults['filter'] !== null) {

                $value = null;

                if (array_key_exists($option, $evaluated)) {
                    $value = $evaluated[$option];
                }

                if (!call_user_func($defaults['filter'], $value)) {
                    if ($defaults['filterValue'] !== null) {
                        $evaluated[$option] = $defaults['filterValue'];
                    } else if ($defaults['defaults'] !== null) {
                        $evaluated[$option] = $defaults['defaults'];
                    } else {
                        throw new OptionsValueException(
                            sprintf(
                                'Value "%s" mismatch for option "%s".',
                                print_r($value, true),
                                $option
                            )
                        );
                    }
                }
            }

        }

        return $evaluated;
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
    private function getSuffixForOption ($option)
    {
        if (!array_key_exists($option, $this->optionMap)) {
            return '';
        }

        $optional  = $this->options[ $this->optionMap[$option] ]['optionalValue'];
        $boolean   = $this->options[ $this->optionMap[$option] ]['boolean'];
        $countable = $this->options[ $this->optionMap[$option] ]['isCountable'];
        $suffix = '';

        if ($boolean || $countable) {
            return '';
        }

        if (strlen($option) === 1) {

            if ($optional) {
                $suffix = '::';
            } else {
                $suffix = ':';
            }

        } else {

            if ($optional) {
                $suffix = '==';
            } else {
                $suffix = '=';
            }

        }

        return $suffix;
    } // end getSuffixForOption ()

    /**
     * Mangles an option name if it contains a dash (-) such that it's
     * accessible as a PHP property.
     * @param  string $name Option name.
     * @return array       Array containing mangled names.
     */
    private function mangle ($name)
    {
        if (strpos($name, '-') === false) {
            return array($name);
        }

        $parts = explode('-', $name);
        $underscoreAlias = implode('_', $parts);


        $first = array_shift($parts);
        $rest = array_map('ucfirst', $parts);
        $camelCaseAlias = $first.implode('', $rest);

        return array($name, $underscoreAlias, $camelCaseAlias);
    } // end mangle ()

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
        // Figure out which options have values or optional values.
        foreach ($this->optionMap as $option => $master) {

            $appendOpts($option, $this->getSuffixForOption($option));

        }

        return $this->getopt->getopt2(
            $this->args,
            $shortOpts,
            $longOpts,
            true
        );
    } // end parseArgs ()
} // end OptionBuilder