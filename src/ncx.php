<?php namespace App;

// Get command line options
$opts = "i:o:";
$options = getopt($opts);

// Load TOC engine
require_once('lib/Transform.php');
require_once('lib/Ncx.php');
$ncx = new lib\Ncx();
$ncx->initializeInput($options['i']);
$ncx->initializeOutput();

// Convert TOC to NCX
$ncx->convert();

// Save output file
file_put_contents($options['o'], $ncx->finalize());
