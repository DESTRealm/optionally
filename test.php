<?php

print_r($_SERVER['argv']);

$_SERVER['argv'] = array('test.php', '-c5', '-d', '-f6');
global $argv;
$argv = $_SERVER['argv'];

/*print "Console_Getopt reports:\n";

require 'lib/Getopt.php';

$cg = new Console_Getopt();
print_r($cg->readPHPArgv());*/

print_r(getopt('c::f:d'));
