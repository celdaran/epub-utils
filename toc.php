<?php namespace App;

// Get command line options
$opts = "i:o:";
$options = getopt($opts);

// Load TOC engine
require_once('lib/Toc.php');
$toc = new lib\Toc();
$toc->initializeOutput();

// Process files
processDirectory($toc, $options['i']);

// Save output package
file_put_contents($options['o'] . '/xhtml/', $toc->finalize());

function processDirectory(lib\Toc $toc, string $inputDir)
{
    // Get files
    $files = scandir($inputDir);

    // Process files
    foreach ($files as $file) {

        $inputFileName = $inputDir . '/' . $file;
        if (substr($inputFileName, -6) !== '.xhtml') {
            continue;
        }

        $toc->addChapter($file, $inputDir);

    }
}
