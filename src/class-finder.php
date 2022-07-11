<?php namespace App;

use domDocument;

// Get command line options
$opts = "i:";
$options = getopt($opts);

// Load ClassFinder engine
require_once('lib/ClassFinder.php');

// Process files
$classes = processDirectory($options['i']);
$classes = dedupe($classes);
dump($classes);

function processDirectory(string $inputDir): array
{
    // Get files
    $files = scandir($inputDir);

    // Initialize classes
    $classes = [];

    // Process files
    foreach ($files as $file) {
        $inputFileName = $inputDir . '/' . $file;
        if (substr($inputFileName, -6) !== '.xhtml') {
            continue;
        }

        echo "Processing $inputFileName\n";

        $cf = new lib\ClassFinder();
        $cf->initializeInput($inputFileName);

        $classes[] = [
            'file' => $inputFileName,
            'classes' => $cf->extractClasses(),
        ];
    }

    return $classes;
}

function dedupe(array $classes): array
{
    $dedupedList = [];

    foreach ($classes as $class) {
        foreach ($class['classes'] as $c) {
            if (!in_array($c, $dedupedList)) {
                $dedupedList[] = $c;
            }
        }
    }

    return $dedupedList;
}

function dump(array $classes)
{
    echo "Found the following classes:\n";
    foreach ($classes as $class) {
        echo "  " . $class . "\n";
    }
}
