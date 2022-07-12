<?php namespace App\lib;

use domDocument;

class Package
{
    /** @var domDocument */
    private domDocument $output;

    /**
     * Initialize the output Document
     * @param string $template
     */
    public function initializeOutput(string $template)
    {
        $this->output = new domDocument('1.0', 'UTF-8');
        $this->output->load($template);
    }

    /**
     * Add a CSS file to the output Document
     * @param string $fileName
     */
    public function addCss(string $fileName)
    {
        echo "Processing file $fileName\n";

        // Set attributes
        $href = 'css/' . $fileName;
        $id = 'css';
        $mediaType = 'text/css';

        // Add file to document
        $this->addToManifest($href, $id, $mediaType);
    }

    /**
     * Add a cover image file to the output Document
     * @param string $fileName
     */
    public function addCoverImg(string $fileName)
    {
        echo "Processing cover image\n";

        // Set attributes
        $href = $fileName;
        $id = 'img_cover';
        $mediaType = 'image/jpeg';
        $properties = 'cover-image';

        // Add file to document
        $this->addToManifest($href, $id, $mediaType, $properties);
    }

    /**
     * Add an image file to the output Document
     * @param string $fileName
     * @param int $counter
     */
    public function addImg(string $fileName, int $counter)
    {
        echo "Processing file $fileName\n";

        // Set attributes
        $href = 'images/' . $fileName;
        $id = sprintf('img_%04d', $counter);
        $mediaType = (substr($fileName, -3) === 'png') ? 'image/png' : 'image/jpeg';

        // Add file to document
        $this->addToManifest($href, $id, $mediaType);
    }

    /**
     * Add a font file to the output Document
     * @param string $fileName
     * @param int $counter
     */
    public function addFont(string $fileName, int $counter)
    {
        echo "Processing file $fileName\n";

        // Set attributes
        $href = 'fonts/' . $fileName;
        $id = sprintf('font_%04d', $counter);
        $mediaType = 'application/font-sfnt';

        // Add file to document
        $this->addToManifest($href, $id, $mediaType);
    }

    /**
     * Add an XHTML file to the output Document
     * @param string $fileName
     * @param string|null $properties
     */
    public function addXhtml(string $fileName, ?string $properties = null)
    {
        echo "Processing file $fileName\n";

        // Deconstruct file name
        $part = explode('.', $fileName);
        $chapterNumber = sprintf('%04d', (int)$part[0]);

        // Set attributes
        $href = 'xhtml/' . $fileName;
        $id = 'c' . $chapterNumber;
        $mediaType = 'application/xhtml+xml';

        // Add file to document
        $this->addToManifest($href, $id, $mediaType, $properties);
        $this->addToSpine($id);
    }

    /**
     * Generic function to add an element to the output Document's manifest
     * @param string $href
     * @param string $id
     * @param string $mediaType
     * @param string|null $properties
     */
    private function addToManifest(string $href, string $id, string $mediaType, ?string $properties = null)
    {
        $manifest = $this->output->getElementsByTagName('manifest');
        $manifest = $manifest->item(0);

        $item = $this->output->createElement('item');
        $item->setAttribute('href', $href);
        $item->setAttribute('id', $id);
        $item->setAttribute('media-type', $mediaType);

        if ($properties) {
            $item->setAttribute('properties', $properties);
        }

        $manifest->appendChild($item);
    }

    /**
     * Generic function to add an element to the output Document's spine
     * @param string $idref
     */
    private function addToSpine(string $idref)
    {
        $spine = $this->output->getElementsByTagName('spine');
        $spine = $spine->item(0);

        $itemref = $this->output->createElement('itemref');
        $itemref->setAttribute('idref', $idref);

        $spine->appendChild($itemref);
    }

    /**
     * Finalize processing and return output Document
     * @return string
     */
    public function finalize(): string
    {
        // Convert dom to xml
        $this->output->formatOutput = true;
        $xml = $this->output->saveXML();

        // Variables
        $uuid = '0a46e3bc-c949-490f-a4e7-d128ce687f4f';
        $modifedDate = date('Y-m-d\TH:i:s\Z');
        $publishedDate = substr($modifedDate, 0, 10);

        // Make substitutions
        $xml = str_replace('__UUID__', $uuid, $xml);
        $xml = str_replace('__MODIFIED_DATE___', $modifedDate, $xml);
        $xml = str_replace('__PUBLISH_DATE__', $publishedDate, $xml);

        // That should do it
        return $xml;
    }

}
