<?php

namespace DESTRealm\Optionally\Exceptions;

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
class GetoptException extends OptionallyException {}