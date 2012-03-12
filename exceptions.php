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