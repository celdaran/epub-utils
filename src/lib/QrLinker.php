<?php namespace App\lib;

use domDocument;
use DOMNode;
use DOMXPath;

class QrLinker
{
    /** @var string */
    private string $fileName;

    /** @var domDocument */
    private domDocument $input;

    /** @var Transform */
    private Transform $transform;

    /** @var array lookup */
    private array $lookup;

    /**
     * @param array $lookup
     */
    public function __construct(array $lookup)
    {
        $this->lookup = $lookup;
    }

    /**
     * Initialize the input Document
     * @param string $fileName
     */
    public function initializeInput(string $fileName)
    {
        $this->fileName = $fileName;

        $this->input = new domDocument('1.0', 'UTF-8');
        $this->input->load($fileName);

        $input = file_get_contents($fileName);
        $this->transform = new Transform();
        $this->transform->initializeInput($input);
    }

    public function linkify(): bool
    {
        /** @var DOMNode $node */
        $node = $this->getElement();

        if ($node !== null) {
            // Get href
            $href = $this->getHref();
            if ($href === '404') {
                echo "Could not find URL for " . $this->fileName . "\n";
                return false;
            }

            // If we have one, then create an anchor tag...
            $anchor = $this->input->createElement('a');
            $anchor->setAttribute('href', $href);

            // And swap nodes
            $node->parentNode->replaceChild($anchor, $node);
            $anchor->appendChild($node);

            // Done!
            return true;
        }

        return false;
    }

    public function saveFile()
    {
        $xhtml = $this->input->saveXML();
        file_put_contents($this->fileName, $xhtml);
    }

    private function getElement()
    {
        $finder = new DOMXPath($this->input);
        $classname= "qr";
        $nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");
        return $nodes[0];
    }

    private function getHref(): string
    {
        $title = $this->transform->extractTitle();
        $slug = Transform::slugify($title);
        return $this->_getHref($slug);
    }

    private function _getHref(string $slug): string
    {
        foreach ($this->lookup as $item) {
            $lookupSlug = trim($item['slug']);

            $x = md5($lookupSlug);
            $y = md5($slug);

            if ($lookupSlug == $slug) {
                return $item['url'];
            }
        }
        return '404';
    }
}
