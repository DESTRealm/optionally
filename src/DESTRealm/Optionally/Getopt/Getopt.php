<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

namespace DESTRealm\Optionally\Getopt;

use DESTRealm\Optionally\Exceptions\OptionallyException;
use DESTRealm\Optionally\Exceptions\GetoptException;

/* !!! WARNING !!!
 *
 * This file has been modified from its original version. PEAR dependencies have
 * been removed and PEAR error conditions have been replaced with raised
 * GetoptException exceptions. Furthermore, certain GNU getopt-compatible
 * options have been changed and may behave differently from what might
 * otherwise be expected.
 *
 * !!! WARNING !!!
 */

/**
 * PHP Version 5
 *
 * Copyright (c) 1997-2004 The PHP Group
 *
 * This source file is subject to version 3.0 of the PHP license,
 * that is bundled with this package in the file LICENSE, and is
 * available through the world-wide-web at the following url:
 * http://www.php.net/license/3_0.txt.
 * If you did not receive a copy of the PHP license and are unable to
 * obtain it through the world-wide-web, please send a note to
 * license@php.net so we can mail you a copy immediately.
 *
 * @category Console
 * @package  Getopt
 * @author   Andrei Zmievski <andrei@php.net>
 * @license  http://www.php.net/license/3_0.txt PHP 3.0
 * @version  CVS: $Id: Getopt.php 306067 2010-12-08 00:13:31Z dufuz $
 * @link     http://pear.php.net/package/Console_Getopt
 */

/**
 * Command-line options parsing class.
 *
 * @category Console
 * @package  Console_Getopt
 * @author   Andrei Zmievski <andrei@php.net>
 * @license  http://www.php.net/license/3_0.txt PHP 3.0
 * @link     http://pear.php.net/package/Console_Getopt
 */
class Getopt
{

    /**
     * Parses the command-line options.
     *
     * The first parameter to this function should be the list of command-line
     * arguments without the leading reference to the running program.
     *
     * The second parameter is a string of allowed short options. Each of the
     * option letters can be followed by a colon ':' to specify that the option
     * requires an argument, or a double colon '::' to specify that the option
     * takes an optional argument.
     *
     * The third argument is an optional array of allowed long options. The
     * leading '--' should not be included in the option name. Options that
     * require an argument should be followed by '=', and options that take an
     * option argument should be followed by '=='.
     *
     * The return value is an array of two elements: the list of parsed
     * options and the list of non-option command-line arguments. Each entry in
     * the list of parsed options is a pair of elements - the first one
     * specifies the option, and the second one specifies the option argument,
     * if there was one.
     *
     * Long and short options can be mixed.
     *
     * Most of the semantics of this function are based on GNU getopt_long().
     *
     * @param array  $args          an array of command-line arguments
     * @param string $short_options specifies the list of allowed short options
     * @param array  $long_options  specifies the list of allowed long options
     * @param boolean $skip_unknown suppresses Getopt: unrecognized option
     *
     * @return array two-element array containing the list of parsed options and
     * the non-option arguments
     * @access public
     */
    function getopt2($args, $short_options, $long_options = null, $skip_unknown = false)
    {
        return Getopt::doGetopt(2, $args, $short_options, $long_options, $skip_unknown);
    }

    /**
     * This function expects $args to start with the script name (POSIX-style).
     * Preserved for backwards compatibility.
     *
     * @param array  $args          an array of command-line arguments
     * @param string $short_options specifies the list of allowed short options
     * @param array  $long_options  specifies the list of allowed long options
     *
     * @see getopt2()
     * @return array two-element array containing the list of parsed options and
     * the non-option arguments
     */
    function getopt($args, $short_options, $long_options = null, $skip_unknown = false)
    {
        return Getopt::doGetopt(1, $args, $short_options, $long_options, $skip_unknown);
    }

    /**
     * The actual implementation of the argument parsing code.
     *
     * @param int    $version       Version to use
     * @param array  $args          an array of command-line arguments
     * @param string $short_options specifies the list of allowed short options
     * @param array  $long_options  specifies the list of allowed long options
     * @param boolean $skip_unknown suppresses Getopt: unrecognized option
     *
     * @return array
     */
    function doGetopt($version, $args, $short_options, $long_options = null, $skip_unknown = false)
    {

        if (empty($args)) {
            return array(array(), array());
        }

        $non_opts = $opts = array();

        settype($args, 'array');

        if ($long_options) {
            sort($long_options);
        }

        /*
         * Preserve backwards compatibility with callers that relied on
         * erroneous POSIX fix.
         */
        if ($version < 2) {
            if (isset($args[0]{0}) && $args[0]{0} != '-') {
                array_shift($args);
            }
        }

        reset($args);
        while (list($i, $arg) = each($args)) {
            /* The special element '--' means explicit end of
               options. Treat the rest of the arguments as non-options
               and end the loop. */
            if ($arg == '--') {
                $non_opts = array_merge($non_opts, array_slice($args, $i + 1));
                break;
            }

            if ($arg{0} != '-') { // || (strlen($arg) > 1 && $arg{1} == '-' && !$long_options)) {
                $non_opts = array_merge($non_opts, array_slice($args, $i));
                break;
            } elseif (strlen($arg) > 1 && $arg{1} == '-') {
                $error = Getopt::_parseLongOption(substr($arg, 2),
                                                          $long_options,
                                                          $opts,
                                                          $args,
                                                          $skip_unknown);
            } elseif ($arg == '-') {
                // - is stdin
                $non_opts = array_merge($non_opts, array_slice($args, $i));
                break;
            } else {
                $error = Getopt::_parseShortOption(substr($arg, 1),
                                                           $short_options,
                                                           $opts,
                                                           $args,
                                                           $skip_unknown);
            }
        }

        return array($opts, $non_opts);
    }

    /**
     * Parse short option
     *
     * @param string     $arg           Argument
     * @param string[]   $short_options Available short options
     * @param string[][] &$opts
     * @param string[]   &$args
     * @param boolean    $skip_unknown suppresses Getopt: unrecognized option
     *
     * @access private
     * @return void
     */
    function _parseShortOption($arg, $short_options, &$opts, &$args, $skip_unknown)
    {
        for ($i = 0; $i < strlen($arg); $i++) {
            $opt     = $arg{$i};
            $opt_arg = null;

            /* Try to find the short option in the specifier string. */
            if (($spec = strstr($short_options, $opt)) === false || $arg{$i} == ':') {
                if ($skip_unknown === true) {
                    break;
                }

                throw new GetoptException('Unrecognized option: '.$opt,
                    OptionallyException::UNRECOGNIZED_OPTION
                );
            }

            if (strlen($spec) > 1 && $spec{1} == ':') {
                if (strlen($spec) > 2 && $spec{2} == ':') {
                    if ($i + 1 < strlen($arg)) {
                        /* Option takes an optional argument. Use the remainder of
                           the arg string if there is anything left. */
                        $opts[] = array($opt, substr($arg, $i + 1));
                        break;
                    } else if (list(, $opt_arg) = each($args)) {
                        if (Getopt::_isShortOpt($opt_arg) ||
                            Getopt::_isLongOpt($opt_arg)) {
                            $opts[] = array($opt, null);
                            prev($args);
                            break;
                        }
                    }
                } else {
                    /* Option requires an argument. Use the remainder of the arg
                       string if there is anything left. */
                    if ($i + 1 < strlen($arg)) {
                        $opts[] = array($opt,  substr($arg, $i + 1));
                        break;
                    } else if (list(, $opt_arg) = each($args)) {
                        /* Else use the next argument. */;
                        if (Getopt::_isShortOpt($opt_arg)
                            || Getopt::_isLongOpt($opt_arg)) {
                            throw new GetoptException('Option requires argument: '.
                                $opt, OptionallyException::REQUIRES_ARGUMENT
                            );
                        }
                    } else {
                        throw new GetoptException('Option requires argument: '.
                            $opt, OptionallyException::REQUIRES_ARGUMENT
                        );
                    }
                }
            }

            $opts[] = array($opt, $opt_arg);
        }
    }

    /**
     * Checks if an argument is a short option
     *
     * @param string $arg Argument to check
     *
     * @access private
     * @return bool
     */
    function _isShortOpt($arg)
    {
        return strlen($arg) == 2 && $arg[0] == '-'
               && preg_match('/[a-zA-Z]/', $arg[1]);
    }

    /**
     * Checks if an argument is a long option
     *
     * @param string $arg Argument to check
     *
     * @access private
     * @return bool
     */
    function _isLongOpt($arg)
    {
        return strlen($arg) > 2 && $arg[0] == '-' && $arg[1] == '-' &&
               preg_match('/[a-zA-Z]+$/', substr($arg, 2));
    }

    /**
     * Parse long option
     *
     * @param string     $arg          Argument
     * @param string[]   $long_options Available long options
     * @param string[][] &$opts
     * @param string[]   &$args
     *
     * @access private
     * @return void|PEAR_Error
     */
    function _parseLongOption($arg, $long_options, &$opts, &$args, $skip_unknown)
    {
        @list($opt, $opt_arg) = explode('=', $arg, 2);

        $opt_len = strlen($opt);

        for ($i = 0; $i < count($long_options); $i++) {
            $long_opt  = $long_options[$i];
            $opt_start = substr($long_opt, 0, $opt_len);

            $long_opt_name = str_replace('=', '', $long_opt);

            /* Option doesn't match. Go on to the next one. */
            if ($long_opt_name != $opt) {
                continue;
            }

            $opt_rest = substr($long_opt, $opt_len);

            /* Check that the options uniquely matches one of the allowed
               options. */
            if ($i + 1 < count($long_options)) {
                $next_option_rest = substr($long_options[$i + 1], $opt_len);
            } else {
                $next_option_rest = '';
            }

            if ($opt_rest != '' && $opt{0} != '=' &&
                $i + 1 < count($long_options) &&
                $opt == substr($long_options[$i+1], 0, $opt_len) &&
                $next_option_rest != '' &&
                $next_option_rest{0} != '=') {

                throw new GetoptException('Ambiguous option: '.$opt,
                    OptionallyException::AMBIGUOUS_OPTION
                );
            }

            if (substr($long_opt, -1) == '=') {
                if (substr($long_opt, -2) != '==') {
                    /* Long option requires an argument.
                       Take the next argument if one wasn't specified. */;
                    if (!strlen($opt_arg) && !(list(, $opt_arg) = each($args))) {
                        $msg = "Getopt: option requires an argument --$opt";
                        throw new GetoptException($msg);
                    }

                    if (Getopt::_isShortOpt($opt_arg)
                        || Getopt::_isLongOpt($opt_arg)) {
                        throw new GetoptException('Option requires argument: '.
                            $opt, OptionallyException::REQUIRES_ARGUMENT
                        );
                    }
                } else {
                    if (Getopt::_isShortOpt(current($args)) ||
                        Getopt::_isLongOpt(current($args))) {
                        $opts[] = array('--'.$opt, $opt_arg);
                    } else {
                        if ($opt_arg === null) {
                            $opts[] = array('--'.$opt, current($args));
                            next($args);
                        } else {
                            $opts[] = array('--'.$opt, $opt_arg);
                        }
                    }
                    break;
                }
            } else if ($opt_arg) {
                throw new GetoptException('Argument not allowed: '.$opt_arg,
                    OptionallyException::ARGUMENT_NOT_ALLOWED
                );
            }

            $opts[] = array('--' . $opt, $opt_arg);
            return;
        }

        if ($skip_unknown === true) {
            return;
        }

        throw new GetoptException('Unrecognized option: '.$opt,
            OptionallyException::UNRECOGNIZED_OPTION
        );
    }

    /**
     * Safely read the $argv PHP array across different PHP configurations.
     * Will take care on register_globals and register_argc_argv ini directives
     *
     * @access public
     * @return mixed the $argv PHP array or PEAR error if not registered
     */
    function readPHPArgv()
    {
        global $argv;
        if (!is_array($argv)) {
            if (!@is_array($_SERVER['argv'])) {
                if (!@is_array($GLOBALS['HTTP_SERVER_VARS']['argv'])) {
                    throw new GetoptException('argc/argv error',
                        OptionallyException::ARGC_ARGV_ERROR
                    );
                }
                return $GLOBALS['HTTP_SERVER_VARS']['argv'];
            }
            return $_SERVER['argv'];
        }
        return $argv;
    }

}