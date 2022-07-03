<?php namespace App;

use domDocument;
use domElement;
use DOMImplementation;
use DOMNode;

class Transform
{
    private domDocument $input;
    private domDocument $output;
    private domElement $tag;

    private ?string $currentSection;

    private array $attributeTransforms = [
        'href' => ['from' => 'style.css', 'to' => 'css/manuscript.css']
    ];

    public function initializeInput(string $fileName)
    {
        $this->input = new domDocument;
        $this->input->loadHTML($fileName);
        $this->input->preserveWhiteSpace = false;
    }

    public function initializeOutput()
    {
        $this->output = new domDocument('1.0', 'UTF-8');

        $outputImplementation = new DOMImplementation();
        $docType = $outputImplementation->createDocumentType('html');
        $this->output->appendChild($docType);

        $html = $this->output->createElement('html');
        $this->output->appendChild($html);

        $attr = $this->output->createAttribute('xmlns');
        $attr->value = 'http://www.w3.org/1999/xhtml';
        $html->appendChild($attr);

        $attr = $this->output->createAttribute('xmlns:epub');
        /** @noinspection HttpUrlsUsage */
        $attr->value = 'http://www.idpf.org/2007/ops';
        $html->appendChild($attr);

        $attr = $this->output->createAttribute('lang');
        $attr->value = 'en';
        $html->appendChild($attr);
    }

    public function process()
    {
        $inputRoot = $this->input->documentElement;

        // Loop over all document elements
        foreach ($inputRoot->childNodes as $node) {
            if ($node->nodeName === 'head') {
                $this->dumpHead($node);
            }

            if ($node->nodeName === 'body') {
                $this->dumpBody($node);
            }
        }
    }

    private function dumpHead(DOMNode $node)
    {
        $outputRoot = $this->output->documentElement;

        $head = $this->output->createElement('head');
        $outputRoot->appendChild($head);

        // For the head, just copy every tag and attribute as-is
        foreach ($node->childNodes as $childNode) {
            $this->copyTag($head, $childNode);
        }
    }

    private function dumpBody(DOMNode $node)
    {
        $outputRoot = $this->output->documentElement;

        if ($node->nodeName === 'body') {
            $body = $this->output->createElement('body');
            $outputRoot->appendChild($body);

            foreach ($node->childNodes as $childNode) {
                $this->currentSection = null;
                $div = null;
                if ($childNode->nodeName !== '#text') {
                    $this->tag = $this->output->createElement($childNode->nodeName);
                    $body->appendChild($this->tag);

                    foreach ($childNode->attributes as $attribute) {
                        $attr = $this->output->createAttribute($attribute->nodeName);
                        $attr->value = $attribute->nodeValue;
                        $this->tag->appendChild($attr);
                    }

                    if ($childNode->nodeName === 'div') {
                        foreach ($childNode->childNodes as $grandChildNode) {
                            $recipeNodeName = $grandChildNode->nodeName;
                            $recipeNodeValue = $grandChildNode->nodeValue;

                            if ($recipeNodeName !== '#text') {
                                if (($recipeNodeName === 'h1') && (1 === 1)) {
                                    $this->currentSection = 'title';
                                    $this->documentTitle = $recipeNodeValue;
                                    $div = $this->writeHeading('h2');
                                } elseif (($recipeNodeName === 'div') && ($grandChildNode->attributes['class'] === 'chapter-content')) {
                                    $this->currentSection = 'images';
                                    $div = $this->writeHeading();
                                } elseif (($recipeNodeName === 'p') && ($recipeNodeValue === 'Stats')) {
                                    $this->currentSection = 'stats';
                                    $div = $this->writeHeading();
                                } elseif (($recipeNodeName === 'p') && ($recipeNodeValue === 'Ingredients')) {
                                    $this->currentSection = 'ingredients';
                                    $div = $this->writeHeading();
                                } elseif (($recipeNodeName === 'p') && ($recipeNodeValue === 'Method')) {
                                    $this->currentSection = 'method';
                                    $div = $this->writeHeading();
                                } elseif (($recipeNodeName === 'p') && ($recipeNodeValue === 'Nutrition')) {
                                    $this->currentSection = 'nutrition';
                                    $div = $this->writeHeading();
                                } else {
                                    echo "$recipeNodeName = $recipeNodeValue (section {$this->currentSection})\n";
                                    if ($this->currentSection === null) {
                                        // this is the opening: qr code, photo, and blockquote
                                        // so process accordingly
                                    } else {
                                        // We're underway with a section
                                    }
                                    $yyz = $this->output->createElement($recipeNodeName);
                                    $yyz->setAttribute('class', $this->currentSection);
                                    if ($this->currentSection === null) {
                                        $this->tag->appendChild($yyz);
                                    } else {
                                        $div->appendChild($yyz);
                                    }

                                    foreach ($grandChildNode->attributes as $attribute) {
                                        $attr = $this->output->createAttribute($attribute->nodeName);
                                        $attr->value = $attribute->nodeValue;
                                        $yyz->appendChild($attr);
                                    }

                                    if ($recipeNodeValue) {
                                        $yyz->nodeValue = $recipeNodeValue;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    private function copyTag(DOMNode $parentNode, DOMNode $node)
    {
        $nodeName = $node->nodeName;
        $nodeValue = $node->nodeValue;

        if ($nodeName !== '#text') {
            $tag = $this->output->createElement($nodeName);
            $parentNode->appendChild($tag);

            foreach ($node->attributes as $attribute) {
                // Any transformations?
                if (in_array($attribute->nodeName, array_keys($this->attributeTransforms))) {
                    $attributeValue = $this->attributeTransforms[$attribute->nodeName]['to'];
                } else {
                    $attributeValue = $attribute->nodeValue;
                }

                // Set attribute value
                $tag->setAttribute($attribute->nodeName, $attributeValue);
            }

            if ($nodeValue) {
                $tag->nodeValue = $nodeValue;
            }
        }
    }

    private function writeHeading(string $tag = 'h3'): DomElement
    {
        // Set $type and $text
        $type = strtolower($this->currentSection) . '-heading';
        $text = ucfirst($this->currentSection);

        // Create surrounding div
        $div = $this->output->createElement('div');
        $div->setAttribute('class', $this->currentSection);
        $this->tag->appendChild($div);

        // Create h3 tag
        $heading = $this->output->createElement($tag);
        $div->appendChild($heading);
        $heading->nodeValue = $text;

        // Add class attribute
        $attr = $this->output->createAttribute('class');
        $attr->value = $type;
        $heading->appendChild($attr);

        return $div;
    }
  
    public function finalize()
    {
        $this->output->formatOutput = true;
        echo $this->output->saveXML();
    }
}