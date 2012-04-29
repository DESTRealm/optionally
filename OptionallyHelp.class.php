<?php

namespace org\destrealm\utilities\optionally;

/**
 * Optionally help generator.
 *
 * This class provides a mechanism for automatically generating usage text for
 * Optionally options. Client libraries aren't required to use this, but they
 * will receive its benefits for free.
 */
class OptionallyHelp
{

    /**
     * Buffer between options and their associated help text, in spaces.
     * @var integer
     */
    private $buffer = 2;

    /**
     * Option cutoff before placing it on a separate line.
     * @var integer
     */
    private $cutoff = 20;

    /**
     * Maximum output columns.
     * @var integer
     */
    private $columns = 80;

    /**
     * Intermediate genearted help.
     * @var array
     */
    private $help = array();

    /**
     * Indentation for aliases as displayed following the primary option.
     * @var integer
     */
    private $indentAliases = 4;

    /**
     * Maximum length of all options.
     * @var integer
     */
    private $maxLength = 0;

    /**
     * Options reference passed through from Optionally. This contains other
     * data useful for creating options and their output.
     * @var array
     */
    private $options = array();

    /**
     * Overall indent applied to all options output. This helps visually offset
     * the generated options from usage text.
     * @var integer
     */
    private $overallIndent = 2;

    /**
     * Sets the indentation used for aliases to offset them from their parent
     * options.
     * @param integer $indent Number of spaces to indent aliases.
     */
    public function setAliasIndentation ($indent)
    {
        $this->indentAliases = $indent;
    } // end setAliasIndentation ()

    /**
     * Set the maximum columns for generated output. This controls word wrap.
     * @param integer $columns Maximum columns.
     */
    public function setMaxColumns ($columns)
    {
        $this->columns = $columns;
    } // end setMaxColumns ()

    /**
     * Sets the buffer to be used for options. This controls how much space
     * appears between an option and its usage text.
     * @param integer $buffer Number of spaces.
     */
    public function setOptionBuffer ($buffer)
    {
        $this->buffer = $buffer;
    } // end setOptionBuffer ()

    /**
     * Sets the option cutoff before an option will appear on its own line. By
     * default, this is 20 characters. Must be less than maxColumns() and
     * ideally should be less than half maxColumns() value (or less; one
     * quarter is ideal).
     * @param integer $cutoff Single-line option cutoff.
     */
    public function setOptionCutoff ($cutoff)
    {
        $this->cutoff = $cutoff;
    } // end setOptionCutoff ()

    /**
     * Adds a description to the option $option and its aliases $aliases, if
     * any.
     * @param string $option          Option.
     * @param string $description     Option description.
     * @param string $arg             Argument name for options that have
     * required or optional values.
     */
    public function addDescription ($option, $description, $arg='')
    {
        $this->help[$option] = array(
            'description' => $description,
            'arg' => $arg,
            'argIsOptional' => false,
            'option' => '', /* Full option and arg. */
            'aliases' => array(), /* Full aliases and their args. */
        );
    } // end addDescription ()

    /**
     * Adds example usage to options. Normally, you shouldn't need this if your
     * options are well-explained, but it can be useful for certain things.
     * Option examples will appear below the usage and will be indented.
     *
     * If you specify aliases, each example $example will be generated for every
     * option.
     * @param string $option          Option.
     * @param string $example         Option example.
     * @param array  $aliases=array() Option aliases, if any.
     */
    public function addExamples ($option, $example, $aliases=array())
    {
        if ((array)$aliases !== $aliases) {
            $aliases = array($aliases);
        }
    } // end addExamples ()

    /**
     * Generates help output.
     * @return string Generated help output.
     */
    public function help ()
    {
        $buf = '';
        $keys = array_keys($this->help);
        sort($keys, SORT_STRING);
        $pos = 0;

        foreach ($keys as $option) {
            $this->parseDescription($option);
        }

        $this->maxLength = $this->calculateMaxLength($keys);

        foreach ($keys as $key) {

            if ($buf !== '') {
                $buf .= "\n";
            }

            $arg = $this->help[$key]['arg'];
            $optional = $this->help[$key]['argIsOptional'];
            $aliases = $this->options[$key]['aliases'];

            // Indented help text, one element per line.
            $help = explode("\n",
                String::indent(
                    String::wrap(
                        String::normalize($this->help[$key]['description']),
                        $this->columns - $this->maxLength - $this->buffer + 1
                    ),
                    $this->maxLength + $this->buffer
                )
            );

            $buf .= $this->helpLine($key, $help);

            foreach ($aliases as $alias) {

                if (count($help) > 0) {
                    $buf .= $this->helpLine($key, $help, $alias);
                } else {
                    $buf .= str_repeat(' ', $this->indentAliases).
                        $this->toOption(
                            $alias,
                            $arg,
                            $optional)."\n";
                }

            }

            if (count($help) > 0) {
                $buf .= implode("\n", $help);
            }

            if (substr($buf, -1) !== "\n") {
                $buf .= "\n";
            }

        }
        return $buf;
    } // end help ()

    /**
     * Sets the local options reference to that used internally by Optionally
     * and processed by Options. This is used mostly to determine how to
     * display option arguments.
     *
     * This method is guaranteed to be called before $this->help().
     * @param array $options=array() [description]
     */
    public function setOptions ($options=array())
    {
        $this->options = $options;
    } // end $options ()

    public function show ()
    {

    } // end show ()

    /**
     * Calculates the maximum length of all options. This is used to determine
     * exactly how much indentation is needed in effort to fit options ahead
     * of their usage text. This value is also compared against $this->cutoff to
     * determine whether an option must be placed on its own line.
     * @return integer Maximum length of all options.
     */
    private function calculateMaxLength ($options)
    {
        $maxLength = 0;
        foreach ($options as $option) {

            $maxLength = max(
                $maxLength,
                strlen(
                    $this->toOption(
                        $option,
                        $this->help[$option]['arg'],
                        $this->help[$option]['argIsOptional']
                    )
                )
            );

            if (!empty($this->options[$option]['aliases'])) {
                foreach ($this->options[$option]['aliases'] as $alias) {

                    $maxLength = max(
                        $maxLength,
                        strlen(
                            $this->toOption(
                                $alias,
                                $this->help[$option]['arg'],
                                $this->help[$option]['argIsOptional']
                            )
                        )
                    );

                }
            }

        }

        return $maxLength;
    } // end calculateMaxLength ()

    private function getArg ($option)
    {
        if (array_key_exists($option, $this->help)) {
            return $this->help[$option]['arg'];
        }

        return '';
    } // end getArg ()

    private function getShortArg ($option)
    {
        if (array_key_exists($option, $this->help)) {
            return $this->help[$option]['shortArg'];
        }

        return '';
    } // end getShortArg ()

    /**
     * Determines if the option $option has an optional value or not.
     * @param string $option Option to check.
     * @return boolean Returns true if the option has an optional value, false
     * otherwise.
     */
    private function hasOptionalValue ($option)
    {
        return array_key_exists($option, $this->options) &&
            !$this->options[$option]['boolean'] && (
                $this->options[$option]['value'] &&
                $this->options[$option]['optionalValue']
            );
    } // end hasOptionalValue ()

    /**
     * Determines if the option $option has a required value or not.
     * @param  string  $option Option to check.
     * @return boolean Returns true if the option has a required value, false
     * otherwise.
     */
    private function hasRequiredValue ($option)
    {
        return array_key_exists($option, $this->options) &&
            !$this->options[$option]['boolean'] && (
                $this->options[$option]['value'] &&
                !$this->options[$option]['optionalValue']
            );
    } // end hasRequiredValue ()

    /**
     * Generates a line of help text concatenated together with the option
     * $option indented $indent spaces.
     * @param string $option Option text.
     * @param array &$help   Array of help text. This is modified in-place.
     * @param integer $indent Number of spaces to indent the option $option.
     * @return string Help line merging a single line from $help and $option.
     * If $option is greater than $this->cutoff, $help is left untouched and
     * $option is returned on its own line.
     */
    private function helpLine ($option, &$help, $alias='')
    {
        $buf = '';
        $arg = $this->help[$option]['arg'];
        $optional = $this->help[$option]['argIsOptional'];
        $indent = 0;

        if (!empty($alias)) {
            $option = $alias;
            $indent = $this->indentAliases;
        }

        $option = $this->toOption($option, $arg, $optional);

        if (strlen($option) <= $this->cutoff) {
            $line = array_shift($help);
            if (($optLine = String::replaceIndent(
                    str_repeat(' ', $this->maxLength + $this->buffer),
                    str_repeat(' ', $indent).$option,
                    $line
                )) !== false) {
                $line = $optLine;
            }
            $buf .= $line."\n";
        } else {
            $buf .= $option."\n";
        }

        return $buf;
    } // end helpLine ()

    private function parseDescription ($option)
    {
        $arg = 'value';
        $optional = false;
        $replacement = '';
        $matches = array();
        $description = $this->help[$option]['description'];

        if (!empty($this->help[$option]['arg'])) {
            $arg = $this->help[$option]['arg'];
        }

        // Matches %@ and %@name for the arg name.
        if (preg_match_all(
            '#(?:[^%]{1})(%@([_A-Za-z0-9]*))#',
            $description,
            $matches,
            PREG_SET_ORDER)) {
            $arg = $matches[0][2];
        }

        if ($this->options[$option]['value']) {
            if ($this->options[$option]['optionalValue']) {
                $replacement = '\\1['.$argName.']';
                $optional = true;
            } else {
                $replacement = '\\1<'.$argName.'>';
                $arg = '<'.$argName.'>';
            }
        }

        $description = preg_replace(
            '#([^%]{1})(%@([_A-Za-z0-9]+))#',
            $replacement,
            $description
        );
        $description = preg_replace(
            '#([^%]{1})(%@)#',
            $replacement,
            $description
        );
        $description = preg_replace(
            '#([^%]{1})(%arg)#',
            $replacement,
            $description
        );

        $help['description'] = $description;
        $help['arg'] = $arg;
        $help['argIsOptional'] = $optional;

    } // end parseDescription ()

    private function toOption ($option, $arg='', $optional=false)
    {
        if (strlen($option) === 1) {
            $option = '-'.$option;

            if ($arg === '') {
                return $option;
            }

            if ($optional) {
                $option .= '[ '.$arg.']';
            } else {
                $option .= ' <'.$arg.'>';
            }

        }

        $option = '--'.$option;

        if ($arg === '') {
            return $option;
        }

        if ($optional) {
            $option .= '[=]['.$arg.']';
        } else {
            $option .= '[=]<'.$arg.'>';
        }

        return $option;

    } // end toOption ()

} // end OptionallyHelp