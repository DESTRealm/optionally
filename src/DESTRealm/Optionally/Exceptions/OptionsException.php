<?php

namespace DESTRealm\Optionally\Exceptions;

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
class OptionsException extends OptionallyException {}