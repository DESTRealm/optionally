<?php

namespace org\destrealm\utilities;

use Exception;

/**
 * Optionally's master exception.
 */
class OptionallyException extends Exception
{
    const UNRECOGNIZED_OPTION   = 0x00000001;
    const REQUIRES_ARGUMENT     = 0x00000002;
    const AMBIGUOUS_OPTION      = 0x00000004;
    const ARGUMENT_NOT_ALLOWED  = 0x00000008;
    const ARGC_ARGV_ERROR       = 0x00000010;
} // end OptionallyException

class OptionallyGetoptException extends OptionallyException
{

} // end OptionallyException

class OptionallyParserException extends OptionallyException
{
    private $option = '';

    public function __construct ($message, $option='', $code=0, $exception=null)
    {
        $this->option = $option;

        parent::__construct($message, $code, $exception);
    } // end constructor

    /**
     * Returns the option that may have triggered this exception. getOption()
     * isn't guaranteed to return useful information.
     * @return string Option string if an option was encountered during this
     * exception; the empty string otherwise.
     */
    public function getOption () { return $this->option; }
} // end OptionallyParserException