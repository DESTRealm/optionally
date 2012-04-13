<?php

namespace org\destrealm\utilities\optionally;

class OptionallyHelp
{

    private $buffer = 2;

    private $cutoff = 20;

    private $columns = 80;

    private $help = array();

    private $maxLength = 0;

    public function __construct ()
    {

    } // end constructor

    public function addDescription ($option, $description, $aliases=array())
    {
        if ((array)$aliases !== $aliases) {
            $aliases = array($aliases);
        }

        $this->maxLength = max(strlen($option), $this->maxLength);

        foreach ($aliases as $alias) {
            $this->maxLength = max(strlen($option), $this->maxLength);
        }

        $this->help[$option] = array(
            'description' => $this->normalize($description),
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

            /*$alias = 0;
            $line = '';

            if (strlen($key) <= $this->cutoff) {
                $line .= $key . str_repeat(' ', $this->buffer);
            } else {
                $buf .= $key."\n";
            }

            while ($pos < strlen($this->help[$key]['description'])) {
                $line .= substr(
                    $this->help[$key]['description'],
                    $pos,
                    $this->columns - strlen($line) - $this->buffer
                );

                $buf .= $line."\n";
            }*/
        }
    } // end help ()

    public function show ()
    {

    } // end show ()

} // end OptionallyHelp