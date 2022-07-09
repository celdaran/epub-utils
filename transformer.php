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

    if (is_file($inputFileName) && (substr($inputFileName, -6) === '.xhtml')) {
        $chapterNumber = $r->getChapterNumber($file);

        // Read file
        $input = file_get_contents($inputFileName);

        // Get type
        $x = new lib\Transform();
        $x->initializeInput($input);
        $isRecipe = $x->isRecipe();

        if ($isRecipe) {
            // Process file
            $x->initializeOutput();
            $x->process();
            $output = $x->finalize();

            // Write file output
            $outputFileName = sprintf($options['o'] . '/xhtml/' . '%s.%s.xhtml', $chapterNumber, $output['slug']);
            file_put_contents($outputFileName, $output['xhtml']);
        } else {
            // Copy file as is
            // TODO: consider splitting $x->process() into processRecipe() and processOther() and simplify this if/else block
            $slug = $x->extractSlug();
            $outputFileName = sprintf($options['o'] . '/xhtml/' . '%s.%s.xhtml', $chapterNumber, $slug);
            copy($inputFileName, $outputFileName);
        }

    }
}

