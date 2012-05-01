<?php

require 'phar://Optionally/vendor/symfony/class-loader/Symfony/Component/ClassLoader/UniversalClassLoader.php';

use Symfony\Component\ClassLoader\UniversalClassLoader;

$loader = new UniversalClassLoader();
$loader->registerNamespaces(array('DESTRealm' => 'phar://Optionally/src'));
$loader->register();

__HALT_COMPILER();