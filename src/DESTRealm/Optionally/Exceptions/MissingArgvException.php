<?php

namespace DESTRealm\Optionally\Exceptions;

/**
 * Forgotten ->argv() exception.
 *
 * This exception is thrown whenever it appears likely that the user forgot to
 * call ->argv() after setting up their options.
 */
class MissingArgvException extends OptionallyException {}