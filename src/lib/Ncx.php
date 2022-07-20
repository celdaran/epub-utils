<?php namespace App\lib;

use domDocument;
use App\lib\Transform;

class Ncx
{
    /** @var domDocument */
    private domDocument $input;

    /** @var domDocument */
    private domDocument $output;

    /**
     * Initialize the input Document
     * @param string $input
     */
    public function initializeInput(string $input)
    {
        $this->input = new domDocument('1.0', 'UTF-8');
        $this->input->load($input);
    }

    /**
     * Initialize the output Document
     * @param string $template
     */
    public function initializeOutput(string $template = 'src/template/toc.ncx')
    {
        $this->output = new domDocument('1.0', 'UTF-8');
        $this->output->load($template);
    }

    /*
    <!-- single level -->
    <navPoint id="content_10_egg-fried-rice" playOrder="10" class="chapter">
        <navLabel>
            <text>Egg Fried Rice</text>
        </navLabel>
        <content src="10_egg-fried-rice.xhtml"/>
    </navPoint>

    <!-- nested -->
    <navPoint id="content_12_chapter-10" playOrder="12" class="chapter">
        <navLabel>
            <text>Chapter 10</text>
        </navLabel>
        <content src="12_chapter-10.xhtml"/>
        <navPoint id="content_12_chapter-10_heading-1" playOrder="13" class="chapter">
            <navLabel>
                <text>Pasta Carbonara</text>
            </navLabel>
            <content src="12_chapter-10.xhtml#heading-1"/>
        </navPoint>
    </navPoint>
    */

    /**
     * @param string $fileName
     */
    public function convert()
    {
        // Get the top level toc
        $toc = $this->input->getElementsByTagName('ol');
        $toc = $toc->item(0);

        // And the same for the output
        $navMap = $this->output->getElementsByTagName('navMap');
        $navMap = $navMap->item(0);

        $counter = 0;

        // This is the toc1 loop
        /** @var \DOMNode $childNode */
        foreach ($toc->childNodes as $childNode) {
            if ($childNode->nodeName !== '#text') {
                $counter++;
//                echo "Found a TOC1 thing: " . $childNode->nodeName . ' = ' . $childNode->nodeValue . "\n";
                $span = $childNode->firstChild;
                echo "Found a TOC1 thing: " . $span->nodeName . ' = ' . $span->nodeValue . "\n";
//                var_dump($childNode);

                // add:toc1
                $navPoint = $this->addNavPoint($navMap, $span->nodeValue, '#', $counter);

                /** @var \DOMNode $grandChildNode */
                $toc2 = $childNode->childNodes[2];
                foreach ($toc2->childNodes as $grandChildNode) {
                    if ($grandChildNode->nodeName !== '#text') {
                        $counter++;
                        // echo "Found a TOC2 thing: " . $grandChildNode->nodeValue . "\n";
                        $anchor = $grandChildNode->firstChild;
                        if ($anchor->nodeName !== '#text') {
                            echo "  Found a TOC2 thing: " . $anchor->nodeName . ' = ' . $anchor->nodeValue . "\n";
                            $href = $anchor->attributes;
                            $href = $href->item(0);
                            // add:toc2
                            $this->addNavPoint($navPoint, $anchor->nodeValue, $href->nodeValue, $counter);
                        }
                    }
                }
            }
        }
    }

    private function addNavPoint(\DOMNode $parent, string $displayText, string $contentText, int $counter): \DOMNode
    {
        $navPoint = $this->output->createElement('navPoint');
        $navPoint->setAttribute('id', Transform::slugify($displayText));
        $navPoint->setAttribute('playOrder', $counter);
        $navPoint->setAttribute('class', 'chapter');

        $navLabel = $this->output->createElement('navLabel');
        $navLabelText = $this->output->createElement('text');
        $navLabelText->nodeValue = $displayText;
        $navLabel->appendChild($navLabelText);
        $navPoint->appendChild($navLabel);

        $content = $this->output->createElement('content');
        if ($contentText === '#') {
            $content->setAttribute('src', sprintf('%04d.%s.xhtml', $counter, Transform::slugify($displayText)));
        } else {
            $content->setAttribute('src', $contentText);
        }
        $navPoint->appendChild($content);

        $parent->appendChild($navPoint);

        return $navPoint;
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
