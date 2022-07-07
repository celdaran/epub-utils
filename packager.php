<?php namespace App;

// Get command line options
$opts = "i:o:t:";
$options = getopt($opts);

// Load transformation engine
require_once('Package.php');
$p = new Package();
$p->initializeOutput($options['t']);

// Process files
processDirectory($p, $options['i'], 'css');
processDirectory($p, $options['i'], 'img');
processDirectory($p, $options['i'], 'xhtml');

// Save output package
file_put_contents($options['o'], $p->finalize());

function processDirectory(Package $p, string $dirName, string $dirType)
{
    $dir = $dirName . '/' . $dirType;

    // Get css files
    $files = scandir($dir);

    // Process files
    foreach ($files as $file) {

        $inputFileName = $dir . '/' . $file;
        if (!is_file($inputFileName)) {
            continue;
        }

        switch ($dirType) {
            case 'css':
                $p->addCss($file);
                break;
            case 'img':
                $p->addImg($file);
                break;
            case 'xhtml':
                if (strpos($file, 'nav.xhtml') === false) {
                    $p->addXhtml($file);
                } else {
                    $p->addXhtml($file, 'nav');
                }
                break;
        }
    }
}
