<?php

print_r($_SERVER['argv']);

print "Console_Getopt reports:\n";

require 'lib/Getopt.php';

$cg = new Console_Getopt();
print_r($cg->readPHPArgv());
