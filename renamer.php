<?php namespace App;

// Get command line options
$opts = "i:";
$options = getopt($opts);

// Load transformation engine
require_once('lib/Renamer.php');
$r = new lib\Renamer();

// Process files
processDirectory($r, $options['i']);

function processDirectory(lib\Renamer $r, string $inputDir)
{
    // Get css files
    $files = scandir($inputDir);

    // Process files
    foreach ($files as $file) {

        $inputFileName = $inputDir . '/' . $file;
        if (substr($inputFileName, -6) !== '.xhtml') {
            // Only process xhtml files
            continue;
        }
        if (preg_match('/[0-9]{4}/', $file)) {
            // Only process files that don't already start with four digits
            continue;
        }

        $part = explode('_', $file);
        $newNumber = $r->getChapterNumber($file);
        if ($newNumber) {
            array_shift($part);
            $newFile = $newNumber . '_' . join('_', $part);
        } else {
            $newFile = $file;
        }

        rename($inputDir . '/'. $file, $inputDir . '/' . $newFile);
    }
}
