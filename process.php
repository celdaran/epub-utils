<?php namespace App;

// Get command line options
$opts  = "i:o:";
$options = getopt($opts);

// Load transformation engine
require_once('Transform.php');

// Get files
$files = scandir($options['i']);
$count = 0;

// Process files
foreach ($files as $file) {

    $inputFileName = $options['i'] . '/' . $file;

    if (is_file($inputFileName) && ($file === '13_coffee-shop-sous-vid.xhtml') /* for testing */) {
        // File number
        $count++;

        // Read file
        $input = file_get_contents($inputFileName);

        // Process file
        $x = new Transform();
        $x->initializeInput($input);
        $x->initializeOutput();
        $x->process();
        $output = $x->finalize();

        // Write file output
        $outputFileName = sprintf($options['o'] . '/' . '%03d.%s.xhtml', $count, $output['slug']);
        file_put_contents($outputFileName, $output['xhtml']);
    }
}
