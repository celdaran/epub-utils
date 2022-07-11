<?php namespace App;

// Get command line options
$opts = "i:f:";
$options = getopt($opts);

// Load stuff
require_once('lib/Renamer.php');
require_once('lib/Inserter.php');

$i = new lib\Inserter($options['i'], $options['f']);
$i->exec('_');
