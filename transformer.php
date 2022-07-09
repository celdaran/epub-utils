<?php namespace App;

// Get command line options
$opts = "i:o:";
$options = getopt($opts);

// Load transformation engine
require_once('lib/Transform.php');
require_once('lib/Renamer.php');

// Instanitate renamer goodies
$r = new lib\Renamer();

// Get files
$files = scandir($options['i']);

// Process files
foreach ($files as $file) {

    $inputFileName = $options['i'] . '/' . $file;

    if (is_file($inputFileName)) {
        $chapterNumber = $r->getChapterNumber($file);

        // Read file
        $input = file_get_contents($inputFileName);

        // Process file
        $x = new lib\Transform();
        $x->initializeInput($input);
        $x->initializeOutput();
        $x->process();
        $output = $x->finalize();

        // Write file output
        $outputFileName = sprintf($options['o'] . '/xhtml/' . '%s.%s.xhtml', $chapterNumber, $output['slug']);
        file_put_contents($outputFileName, $output['xhtml']);
    }
}

