<?php namespace App;

class Transform
{
    private \domDocument $input;
    private \domDocument $output;
    private \domElement $tag;

    public function initializeInput(string $fileName)
    {
        $this->input = new \domDocument;
        $this->input->loadHTML($fileName);
        $this->input->preserveWhiteSpace = false;
    }

    public function initializeOutput()
    {
        $this->output = new \domDocument('1.0', 'UTF-8');

        $outputImplementation = new \DOMImplementation();
        $docType = $outputImplementation->createDocumentType('html');
        $this->output->appendChild($docType);

        $html = $this->output->createElement('html');
        $this->output->appendChild($html);

        $attr = $this->output->createAttribute('xmlns');
        $attr->value = 'http://www.w3.org/1999/xhtml';
        $html->appendChild($attr);
 
        $attr = $this->output->createAttribute('xmlns:epub');
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

    private function dumpHead(\DOMNode $node)
    {
        $outputRoot = $this->output->documentElement;

        $head = $this->output->createElement('head');
        $outputRoot->appendChild($head);

        // For the head, just copy every tag and attribute as-is
        foreach ($node->childNodes as $childNode) {
            $this->copyTag($head, $childNode);
        }
    }

    private function dumpBody(\DOMNode $node)
    {
        $outputRoot = $this->output->documentElement;

        if ($node->nodeName === 'body') {
            $body = $this->output->createElement('body');
            $outputRoot->appendChild($body);

            foreach ($node->childNodes as $childNode) {
                $childNodeName = $childNode->nodeName;
                $childNodeValue = $childNode->nodeValue;

                if ($childNodeName !== '#text') {
                    // echo '<li>' . $childNode->nodeName . ' - ' . $childNode->nodeValue . '</li>';
                    $this->tag = $this->output->createElement($childNodeName);
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
                                if (($recipeNodeName === 'p') && ($recipeNodeValue === 'Stats')) {
                                    $this->writeHeading3('stats-heading');
                                } elseif (($recipeNodeName === 'p') && ($recipeNodeValue === 'Ingredients')) {
                                    $this->writeHeading3('ingredients-heading');
                                } elseif (($recipeNodeName === 'p') && ($recipeNodeValue === 'Method')) {
                                    $this->writeHeading3('method-heading');
                                } else {
                                    $yyz = $this->output->createElement($recipeNodeName);
                                    $this->tag->appendChild($yyz);
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

    private function copyTag(\DOMNode $parentNode, \DOMNode $node)
    {
        $nodeName = $node->nodeName;
        $nodeValue = $node->nodeValue;

        if ($nodeName !== '#text') {
            $tag = $this->output->createElement($nodeName);
            $parentNode->appendChild($tag);

            foreach ($node->attributes as $attribute) {
                $attr = $this->output->createAttribute($attribute->nodeName);
                $attr->value = $attribute->nodeValue;
                $tag->appendChild($attr);
            }

            if ($nodeValue) {
                $tag->nodeValue = $nodeValue;
            }
        }
    }

    private function writeHeading3(string $type)
    {
        // Create h3 tag
        $h3 = $this->output->createElement('h3');
        $this->tag->appendChild($h3);

        // Add class attribute
        $attr = $this->output->createAttribute('class');
        $attr->value = $type;
        $h3->appendChild($attr);
    }
  
    public function finalize()
    {
        $this->output->formatOutput = true;
        echo $this->output->saveXML();
    }
}
