<?php

namespace org\destrealm\utilities\optionally;

use Exception;

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

/**
 * Forgotten ->argv() exception.
 *
 * This exception is thrown whenever it appears likely that the user forgot to
 * call ->argv() after setting up their options.
 */
class OptionallyMissingArgvException extends OptionallyException {}

/**
 * GetOpt exception wrapper.
 *
 * This exception is thrown by the modified Console_Getopt class; rather than
 * generating PEAR errors, which Console_Getopt does by default, it will throw
 * this exception instead.
 *
 * You generally shouldn't capture this exception unless you're fairly certain
 * you need to be made aware of the status of the Getopt parser. If you're
 * seeing this exception crop up in user code, try catching OptionallyException
 * instead.
 */
class OptionallyGetoptException extends OptionallyException {}

/**
 * Options exception
 * 
 * This exception is thrown whenever a required option was expected but not
 * supplied as well as if an option is required if another option wasn't
 * supplied.
 * 
 * IF YOU'RE CAPTURING THIS EXCEPTION, YOU'RE DOING SOMETHING SORELY WRONG.
 * 
 * The two features that throw this exception have extremely limited use cases,
 * and if you're seeing this exception, you should probably reconsider the
 * design of your script or application.
 */
class OptionallyOptionsException extends OptionallyException {}

/**
 * Value exception.
 * 
 * This exception is thrown by the ->test() method and indicates that an
 * option's value failed whatever test you configured. If you're seeing this
 * exception, it's safer to capture OptionallyException instead and print out
 * your script's usage text. Of course, there might be a circumstance where you
 * must know the precise cause of the error; in that case, examining this
 * exception might prove useful.
 */
class OptionallyOptionsValueException extends OptionallyOptionsException {}