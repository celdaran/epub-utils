<?php namespace App\lib;

use domDocument;

class Toc
{
    /** @var domDocument */
    private domDocument $output;

    /**
     * Initialize the output Document
     * @param string $template
     */
    public function initializeOutput(string $template = 'src/template/toc.xhtml')
    {
        $this->output = new domDocument('1.0', 'UTF-8');
        $this->output->load($template);
    }

    /**
     * Add a chapter to the output document
     * @param string $fileName
     * @param string $inputDir
     */
    public function addChapter(string $fileName, string $inputDir)
    {
        echo "Processing file $fileName\n";

        // Add file to document
        $this->addToToc($fileName, $inputDir);
    }

    /**
     * @param string $fileName
     * @param string $inputDir
     */
    private function addToToc(string $fileName, string $inputDir)
    {
        // Extract title
        $recipe = new domDocument('1.0', 'UTF-8');
        $recipe->load($inputDir . '/' . $fileName);
        $title = $recipe->getElementsByTagName('title');
        $title = $title->item(0);

        // Now add li/a tags
        $toc = $this->output->getElementsByTagName('ol');
        $toc = $toc->item(0);

        $item = $this->output->createElement('li');
        $toc->appendChild($item);

        $anchor = $this->output->createElement('a');
        $anchor->setAttribute('href', $fileName);
        $anchor->nodeValue = $title->nodeValue;
        $item->appendChild($anchor);
    }

    /**
     * Finalize processing and return output Document
     * @return string
     */
    public function finalize(): string
    {
        $this->output->formatOutput = true;
        return $this->output->saveXML();
    }
}
