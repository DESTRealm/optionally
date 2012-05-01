<?php

namespace DESTRealm\Optionally\Exceptions;

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
class OptionsValueException extends OptionsException {}