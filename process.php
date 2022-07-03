<?php namespace App;

// Load transformation engine
require_once('Transform.php');

// Get files
$files = scandir('files');
$count = 0;

// Process files
foreach ($files as $file) {

    $inputFileName = "files/$file";

    if (is_file($inputFileName)) {
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
        $outputFileName = sprintf('files/output/%03d.%s.xhtml', $count, $output['slug']);
        file_put_contents($outputFileName, $output['xhtml']);
    }
}
