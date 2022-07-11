<?php namespace App\lib;

use domDocument;
use DOMNode;

class ClassFinder
{
    /** @var domDocument */
    private domDocument $input;

    /**
     * Initialize the intput Document
     * @param string $fileName
     */
    public function initializeInput(string $fileName)
    {
        $this->input = new domDocument('1.0', 'UTF-8');
        $this->input->load($fileName);
    }

    public function extractClasses(): array
    {
        // Start with the body tag
        $body = $this->input->getElementsByTagName('body');
        $body = $body->item(0);

        // Thing
        $foundClasses = [];

        // Loop through each tag and pull back classes recursively
        $children = $body->childNodes;
        foreach ($children as $child) {
            $classesInElement = $this->getClassesInElement($child);
            $foundClasses = array_merge($foundClasses, $classesInElement);
        }

        return $foundClasses;
    }

    private function getClassesInElement(DOMNode $node): array
    {
        $foundClasses = [];

        $attributes = $node->attributes;

        if ($attributes !== null) {
            foreach ($attributes as $attribute) {
                if ($attribute->nodeName === 'class') {
                    $foundClasses[] = $attribute->nodeValue;
                }
            }
        }

        foreach ($node->childNodes as $childNode) {
            $foundClasses = array_merge($foundClasses, $this->getClassesInElement($childNode));
        }

        return $foundClasses;
    }
}
