<?php namespace App;

use App\lib\Transform;

// Get command line options
$opts = "i:";
$options = getopt($opts);

// Load classes
require_once('lib/QrLinker.php');
require_once('lib/Transform.php');

// Load CSV
$lookup = loadLookup();

// Process files
processDirectory($options['i'], $lookup);

function processDirectory(string $inputDir, array $lookup)
{
    // Get files
    $files = scandir($inputDir);

    // Process files
    foreach ($files as $file) {
        $inputFileName = $inputDir . '/' . $file;
        if (substr($inputFileName, -6) !== '.xhtml') {
            continue;
        }

        echo "Processing $inputFileName\n";

        $qr = new lib\QrLinker($lookup);
        $qr->initializeInput($inputFileName);

        $updated = $qr->linkify();
        if ($updated) {
            $qr->saveFile();
        }
    }
}

function loadLookup(): array
{
    // Open CSV
    $file = fopen('src/file/mbk-cfo-ww-qr-codes.csv', 'r');
    $lookup = [];

    // Read lines
    while (($line = fgetcsv($file)) !== false) {
        $lookup[] = [
            'slug' => Transform::slugify($line[0]),
            'title' => $line[0],
            'url' => $line[1],
        ];
    }
    fclose($file);

    // Return loaded data
    return $lookup;
}
