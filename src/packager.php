<?php namespace App;

// Get command line options
$opts = "i:o:t:";
$options = getopt($opts);

// Template?
if (array_key_exists('t', $options)) {
    $template = $options['t'];
} else {
    $template = 'src/template/package.opf';
}

// Load transformation engine
require_once('lib/Package.php');
$p = new lib\Package();
$p->initializeOutput($template);

// Add cover image first
$p->addCoverImg('ebook-cover.jpg');

// Process files
processDirectory($p, $options['i'], 'css');
processDirectory($p, $options['i'], 'images');
processDirectory($p, $options['i'], 'fonts');
processDirectory($p, $options['i'], 'xhtml');

// Save output package
file_put_contents($options['o'], $p->finalize());

function processDirectory(lib\Package $p, string $dirName, string $dirType)
{
    $dir = $dirName . '/' . $dirType;

    // Get css files
    $files = scandir($dir);

    // Image counter
    $counter = 0;

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
            case 'images':
                $p->addImg($file, $counter++);
                break;
            case 'fonts':
                $p->addFont($file, $counter++);
                break;
            case 'xhtml':
                if (strpos($file, 'contents.xhtml') === false) {
                    $p->addXhtml($file);
                } else {
                    $p->addXhtml($file, 'nav');
                }
                break;
        }
    }
}
