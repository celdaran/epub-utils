<?php namespace App;

// Get command line options
$opts = "i:o:";
$options = getopt($opts);

// Load transformation engine
require_once('lib/Transform.php');
require_once('lib/Renamer.php');

// Instantiate renamer goodies
$r = new lib\Renamer();

// Get files
$files = scandir($options['i']);

// Process files
foreach ($files as $file) {

    $inputFileName = $options['i'] . '/' . $file;

    // Instantiate transformer
    $x = new lib\Transform();

    if ($x->fileAllowed($inputFileName)) {
        $chapterNumber = $r->getChapterNumber($file);

        // Read file
        $input = file_get_contents($inputFileName);

        $x->initializeInput($input);
        $x->initializeOutput();

        // Get type
        $isRecipe = $x->isRecipe();

        // Process file
        $x->process($isRecipe);

        // Any final-pass actions
        $x->postProcess($isRecipe);

        // Write file output
        $output = $x->finalize();
        $outputFileName = sprintf($options['o'] . '/xhtml/' . '%s.%s.xhtml', $chapterNumber, $output['slug']);
        file_put_contents($outputFileName, $output['xhtml']);
        echo "Created $outputFileName\n";
    }
}

