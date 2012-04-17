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
     * @param string $argName         Argument name. For options that have
     * required or optional values.
     */
    public function addDescription ($option, $description, $argName='')
    {
        $this->maxLength = max(strlen(OptionallyHelp::toOption($option)),
            $this->maxLength);

        $this->help[$option] = array(
            'description' => $description,
            'argName' => $argName,
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
        $this->parseOptions();
        $buf = '';
        $keys = array_keys($this->help);
        sort($keys, SORT_STRING);
        $pos = 0;

        foreach ($keys as $key) {

            if ($buf !== '') {
                $buf .= "\n";
            }

            $aliases = $this->options[$key]['aliases'];

            // Indented help text, one element per line.
            $help = explode("\n",
                String::indent(
                    String::wrap(
                        $this->help[$key]['description'],
                        $this->columns - $this->maxLength - $this->buffer + 1
                    ),
                    $this->maxLength + $this->buffer
                )
            );

            $buf .= $this->helpLine($key, $help);

            $max = count($aliases);
            for ($i = 0; $i < $max; $i++) {

                if (empty($help)) {
                    break;
                }

                $alias = array_shift($aliases);

                $buf .= $this->helpLine(
                    $alias,
                    $help,
                    $this->indentAliases
                );

            }

            if (!empty($aliases)) {
                $buf .= str_repeat(' ', $this->indentAliases).
                    implode("\n".str_repeat(' ', $this->indentAliases),
                        array_map(function($alias) {return OptionallyHelp::toOption($alias);}, $aliases));
            } else if (!empty($help)) {
                $buf .= implode("\n", $help);
            }

            $buf .= "\n";

        }

        return $buf;
    } // end help ()

    public function setOptions ($options=array())
    {
        $this->options = $options;
    } // end $options ()

    public function show ()
    {

    } // end show ()

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
    private function helpLine ($option, &$help, $indent=0)
    {
        $buf = '';
        $option = OptionallyHelp::toOption($option);

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

    private function parseDescription ($description, $option, $argName='')
    {
        return $description;
    } // end parseDescription ()

    private function parseOptions ()
    {
        foreach ($this->options as $option => $details) {

            if (!array_key_exists($option, $this->help)) {
                continue;
            }

            $aliases = array();
            $this->help[$option] = array_merge($this->help[$option], $details);

            foreach ($details['aliases'] as $alias) {
                $alias = OptionallyHelp::toOption($alias);
                $this->maxLength = max(strlen($alias)+$this->indentAliases,
                    $this->maxLength);
                $aliases[] = $alias;
            }

            $this->help[$option]['aliases'] = $aliases;
            $this->help[$option]['description'] =
                $this->parseDescription(
                    String::normalize(
                        $this->help[$option]['description']
                    ),
                    $option,
                    $this->help[$option]['argName']
                );

        }
    } // end parseOption ()

    public static function toOption ($option)
    {
        if (strlen($option) === 1) {
            return '-'.$option;
        }

        return '--'.$option;
    } // end toOption ()

} // end OptionallyHelp