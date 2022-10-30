<?php namespace App\lib;

use domDocument;
use domElement;
use DOMImplementation;
use DOMNode;
use DOMXPath;

class Transform
{
    private domDocument $input;
    private domDocument $output;
    private domElement $tag;

    private string $documentTitle;
    private ?string $currentSection;

    private array $attributeTransforms = [
        'href' => ['from' => 'style.css', 'to' => '../css/manuscript.css']
    ];

    public function initializeInput(string $file)
    {
        $this->input = new domDocument;
        if ($this->input->loadXML($file) === false) {
            echo "Could not read file\n";
        }
        $this->input->preserveWhiteSpace = false;
        $this->documentTitle = $this->extractTitle();
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

    public function process(bool $isRecipe)
    {
        $inputRoot = $this->input->documentElement;

        // Loop over all document elements
        foreach ($inputRoot->childNodes as $node) {
            if ($node->nodeName === 'head') {
                $this->dumpHead($node);
            }

            if ($node->nodeName === 'body') {
                if ($isRecipe) {
                    $this->dumpBody($node);
                } else {
                    $this->cloneBody($node);
                }
            }
        }
    }

    public function postProcess(bool $isRecipe)
    {
        // This only applies to recipe pages
        if (!$isRecipe)
            return;

        /** @var DOMNode $qr */
        $qr = $this->getElementByClass('qr');

        if ($qr !== null) {
            /** @var DOMNode $top */
            $top = $this->getElementByClass('title');

            $top->insertBefore($qr, $top->firstChild);
        }
    }

    /**
     * @return bool
     */
    public function isRecipe(): bool
    {
        $metaTags = $this->input->getElementsByTagName('meta');
        foreach ($metaTags as $meta) {
            $pageType = $meta->getAttribute('data-page-type');
            if ($pageType === 'nonrecipe') {
                return false;
            }
        }
        return true;
    }

    public function fileAllowed(string $fileName): bool
    {
        // return $fileName === '../mbk-cfo/source/OEBPS/0010_egg-fried-rice.xhtml');

        $allowed = false;
        if (is_file($fileName) && (substr($fileName, -6) === '.xhtml')) {
            // TODO: Add support for .xignore if there are files to be ignored
            $allowed = true;
        }
        return $allowed;
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
                                    // $this->documentTitle = $recipeNodeValue;
                                    $div = $this->writeHeading('h2', $recipeNodeValue);
                                } elseif (($recipeNodeName === 'div') && ($grandChildNode->attributes['class'] === 'chapter-content')) {
                                    $this->currentSection = 'images';
                                    $div = $this->writeHeading('h3', $recipeNodeValue);
                                } elseif (($recipeNodeName === 'p') && ($recipeNodeValue === 'Stats')) {
                                    $this->currentSection = 'stats';
                                    $div = $this->appendToHeading();
                                } elseif (($recipeNodeName === 'p') && ($recipeNodeValue === 'Ingredients')) {
                                    $this->currentSection = 'ingredients';
                                    $div = $this->writeHeading('h3', $recipeNodeValue);
                                } elseif (($recipeNodeName === 'p') && ($recipeNodeValue === 'Method')) {
                                    $this->currentSection = 'method';
                                    $div = $this->writeHeading('h3', $recipeNodeValue);
                                } elseif (($recipeNodeName === 'p') && ($recipeNodeValue === 'Nutrition')) {
                                    $this->currentSection = 'nutrition';
                                    $div = $this->writeHeading('h3', $recipeNodeValue);
                                } elseif (($recipeNodeName === 'p') && ($recipeNodeValue === 'Notes')) {
                                    $this->currentSection = 'notes';
                                    $div = $this->writeHeading('h3', $recipeNodeValue);
                                } elseif (($recipeNodeName === 'p') && ($recipeNodeValue === 'Tip')) {
                                    $this->currentSection = 'tips';
                                    $div = $this->writeHeading('h3', $recipeNodeValue);
                                } elseif (($recipeNodeName === 'p') && ($recipeNodeValue === 'Tips')) {
                                    $this->currentSection = 'tips';
                                    $div = $this->writeHeading('h3', $recipeNodeValue);
                                } else {
                                    // echo "$recipeNodeName = $recipeNodeValue (section {$this->currentSection})\n";
                                    if ($this->currentSection === null) {
                                        // this is the opening: qr code, photo, and blockquote
                                        // so process accordingly
                                        $nextThing = $grandChildNode->childNodes[0];
                                        if ($nextThing->nodeName === 'img') {
                                            $flag = true;
                                            // echo "*** Found a qr code or photo\n";
                                            // echo "***   value: " . $nextThing->nodeValue . "\n";
                                            $yyz = $this->output->createElement('img');
                                            $yyz->setAttribute('class', $this->currentSection);
                                        } else {
                                            $flag = false;
                                            // echo "*** Found a blockquote\n";
                                            // echo "***   value: " . $nextThing->nodeValue . "\n";
                                            $yyz = $this->output->createElement('blockquote');
                                            $yyz->setAttribute('class', $this->currentSection);
                                        }
                                    } elseif ($this->currentSection === 'stats') {
                                        // Special handling to move stats up to a different part of the document
                                        $recipeStats = $this->getElementByClass('recipe-stats');
                                        if ($recipeStats->nodeValue) {
                                            $recipeStats->nodeValue = $recipeStats->nodeValue . ', ' . $recipeNodeValue;
                                        } else {
                                            $recipeStats->nodeValue = $recipeNodeValue;
                                        }
                                    } else {
                                        // These are coming in as <p> but I want to send them out
                                        $yyz = $this->output->createElement('li');
                                        $yyz->setAttribute('class', $this->currentSection);
                                    }

                                    if ($this->currentSection === null) {
                                        $this->tag->appendChild($yyz);
                                    } elseif ($this->currentSection !== 'stats') {
                                        $div->appendChild($yyz);
                                    }

                                    foreach ($grandChildNode->attributes as $attribute) {
                                        $attr = $this->output->createAttribute($attribute->nodeName);
                                        $attr->value = $attribute->nodeValue;
                                        $yyz->appendChild($attr);
                                    }

                                    if ($flag) {
                                        $nextThingImported = $this->output->importNode($nextThing);
                                        /*
                                        $yyz->appendChild($nextThingImported);
                                        */
                                        $imgSrc = $nextThingImported->attributes[0]->nodeValue;
                                        $imgSrc = str_replace('images/', '../images/', $imgSrc);
                                        $imgClass = (substr($imgSrc, -3) === 'jpg' ? 'photo' : 'qr');
                                        $imgAlt = (substr($imgSrc, -3) === 'jpg' ? 'Photo of completed recipe' : 'QR code for WW recipe');
                                        $yyz->removeAttribute('class');
                                        $yyz->setAttribute('src', $imgSrc);
                                        $yyz->setAttribute('class', $imgClass);
                                        $yyz->setAttribute('alt', $imgAlt);
                                    } else {
                                        if ($this->currentSection !== 'stats') {
                                            if ($recipeNodeValue) {
                                                $yyz->nodeValue = htmlspecialchars($recipeNodeValue);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    private function cloneBody(DOMNode $node)
    {
        $outputRoot = $this->output->documentElement;
        $body = $this->output->importNode($node, true);
        $outputRoot->appendChild($body);
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
                    $attributeValue = str_replace(
                        $this->attributeTransforms[$attribute->nodeName]['from'],
                        $this->attributeTransforms[$attribute->nodeName]['to'],
                        $attribute->nodeValue);
                } else {
                    $attributeValue = $attribute->nodeValue;
                }

                // Set attribute value
                $tag->setAttribute($attribute->nodeName, $attributeValue);
            }

            if ($nodeValue) {
                $tag->nodeValue = htmlentities($nodeValue);
            }
        }
    }

    private function writeHeading(string $tag, string $value): DomElement
    {
        // Set $type and $text
        $class = strtolower($this->currentSection) . '-heading';

        // Create surrounding div
        $div = $this->output->createElement('div');
        $div->setAttribute('class', $this->currentSection);
        $this->tag->appendChild($div);

        // Create tag
        $heading = $this->output->createElement($tag);
        $div->appendChild($heading);
        $heading->nodeValue = htmlentities($value);

        // Add class attribute
        $attr = $this->output->createAttribute('class');
        $attr->value = $class;
        $heading->appendChild($attr);

        $ul = $this->output->createElement('ul');
        if ($class !== 'title-heading') {
            $div->appendChild($ul);
        }

        return $ul;
    }

    private function appendToHeading(): DomElement
    {
        // Create new element
        $p = $this->output->createElement('p');
        $p->setAttribute('class', 'recipe-stats');

        $title = $this->getElementByClass('title');

        // Attach it
        $title->appendChild($p);

        // Now grab the QR code, if it exists, and send that out as the next "div"
        $qr = $this->getElementByClass('qr');

        if ($qr === null) {
            return $title;
        } else {
            echo "Found a qr element where none exists\n";
            return $qr;
        }
    }

    private function getElementByClass(string $classname)
    {
        $finder = new DOMXPath($this->output);
        $query = "//*[contains(@class, '$classname')]";
        $nodes = $finder->query($query);
        if ($nodes->length > 0) {
            return $nodes[0];
        } else {
            return null;
        }
    }

    public static function slugify(string $subject): string
    {
        $subject = strtolower($subject);
        $subject = str_replace(' ', '-', $subject);
        $subject = preg_replace('/[\':]/', '', $subject);
        return $subject;
    }

    public function extractTitle(): string
    {
        $title = $this->input->getElementsByTagName('title');
        return $title->item(0)->nodeValue;
    }

    public function finalize(): array
    {
        $this->output->formatOutput = true;
        return [
            'title' => $this->documentTitle,
            'slug' => self::slugify($this->documentTitle),
            'xhtml' => $this->output->saveXML()
        ];
    }
}
