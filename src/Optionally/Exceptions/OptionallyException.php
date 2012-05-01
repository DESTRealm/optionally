<?php

namespace DESTRealm\Optionally\Exceptions;

use \Exception;

/**
 * Optionally's master exception.
 *
 * If you're just interested in capturing Optionally's exceptions and don't
 * particularly care what the source was that triggered it, such as generating
 * a help/usage list if Optionally dies, you should at least catch this
 * exception. All exceptions that Optionally throws inherit from this class,
 * and catching it will catch all of them.
 */
class OptionallyException extends Exception
{
    const UNRECOGNIZED_OPTION   = 0x00000001;
    const REQUIRES_ARGUMENT     = 0x00000002;
    const AMBIGUOUS_OPTION      = 0x00000004;
    const ARGUMENT_NOT_ALLOWED  = 0x00000008;
    const ARGC_ARGV_ERROR       = 0x00000010;
} // end OptionallyException