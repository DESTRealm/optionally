<?php

namespace org\destrealm\utilities\optionally;

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

    public function __construct ()
    {

    } // end constructor

    public function addDescription ($option, $description, $aliases=array())
    {
        if ((array)$aliases !== $aliases) {
            $aliases = array($aliases);
        }

        $option = $this->toOption($option);

        $this->maxLength = max(strlen($option), $this->maxLength);

        foreach ($aliases as &$alias) {
            $alias = $this->toOption($alias);
            $this->maxLength = max(strlen($alias)+$this->indentAliases,
                $this->maxLength);
        }

        $this->help[$option] = array(
            'description' => String::normalize($description),
            'aliases' => $aliases
        );
    } // end addDescription ()

    public function addExamples ($option, $example, $aliases=array())
    {
        if ((array)$aliases !== $aliases) {
            $aliases = array($aliases);
        }
    } // end addExamples ()

    public function help ()
    {
        $buf = '';
        $keys = array_keys($this->help);
        sort($keys, SORT_STRING);
        $pos = 0;

        foreach ($keys as $key) {

            $aliases = $this->help[$key]['aliases'];

            // Indented help text, one element per line.
            $help = explode("\n",
                String::indent(
                    String::wrap(
                        String::normalize($this->help[$key]['description'])
                    ),
                    $this->maxLength + $this->buffer
                )
            );

            if (strlen($key) <= $this->cutoff) {
                $line = array_shift($help);
                if (($optLine = String::replaceIndent(
                        str_repeat(' ', $this->maxLength + $this->buffer),
                        $key,
                        $line
                    )) !== false) {
                    $line = $optLine;
                }
                $buf .= $line."\n";
            } else {
                $buf .= $key."\n";
            }

            $max = count($aliases);
            for ($i = 0; $i < $max; $i++) {

                $alias = array_shift($aliases);

                if (empty($help)) {
                    break;
                }

                if (strlen($alias) <= $this->cutoff) {
                    $line = array_shift($help);
                    $line = String::replaceIndent(
                        str_repeat(' ', $this->maxLength + $this->buffer),
                        str_repeat(' ', $this->indentAliases).$alias,
                        $line
                    );
                    $buf .= $line."\n";
                } else {
                    $buf .= $alias."\n";
                }

            }

            if (!empty($aliases)) {
                $buf .= implode("\n", $aliases);
            } else if (!empty($help)) {
                $buf .= implode("\n", $help);
            }

            $buf .= "\n";

        }

        return $buf;
    } // end help ()

    public function show ()
    {

    } // end show ()

    private function toOption ($option)
    {
        if (strlen($option) === 1) {
            return '-'.$option;
        }

        return '--'.$option;
    } // end toOption ()

} // end OptionallyHelp