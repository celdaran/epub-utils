<?php namespace App;

// Get command line options
$opts = "i:o:";
$options = getopt($opts);

// Process files
processDirectory($options['i'], $options['o']);

function processDirectory(string $inputDir, string $outputDir)
{
    // Get css files
    $files = scandir($inputDir);

    // Process files
    foreach ($files as $file) {

        $inputFileName = $inputDir . '/' . $file;
        if (substr($inputFileName, -6) !== '.xhtml') {
            continue;
        }

        $part = explode('_', $file);
        if (count($part) > 1) {
            $oldNumber = array_shift($part);
            $newNumber = sprintf('%04d', (int)$oldNumber);
            $newFile = $newNumber . '_' . join('_', $part);
        } else {
            $newFile = $file;
        }

        copy($inputDir . '/'. $file, $outputDir . '/' . $newFile);
    }
}
