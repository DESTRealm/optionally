<?php

require './src/DESTRealm/External/SplClassLoader.php';

use DESTRealm\External\SplClassLoader;

$loader = new SplClassLoader('DESTRealm', './src/');
$loader->register();
