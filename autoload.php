<?php

namespace DESTRealm\Optionally;

/**
 * Optionally class loader wrapper.
 *
 * This wrapper is provided mostly to avoid potentially name collisions that
 * would arise from doing everything in the autoload.php script.
 */
class Autoloader
{
    /**
     * Loads and initializes SplClassLoader.
     */
    public static function load ()
    {
        require './src/DESTRealm/External/SplClassLoader.php';

        $loader = new \DESTRealm\External\SplClassLoader('DESTRealm', './src/');
        $loader->register();
    } // end load ()
} // end Autoloader
