<?php namespace App\lib;

class Renamer
{
    /**
     * @param string $fileName
     * @param string $delimiter
     * @return string
     */
    public function getChapterNumber(string $fileName, string $delimiter = '_'): string
    {
        $part = explode($delimiter, $fileName);
        if (count($part) > 1) {
            $oldNumber = array_shift($part);
            $newNumber = sprintf('%04d', (int)$oldNumber);
        } else {
            $newNumber = '';
        }

        return $newNumber;
    }

    /**
     * @param string $fileName
     * @param string $delimiter
     * @return string
     */
    public function getChapterName(string $fileName, string $delimiter = '_'): string
    {
        $part = explode($delimiter, $fileName);
        if (count($part) > 1) {
            $name = $part[1];
            $name = str_replace('.xhtml', '', $name);
        } else {
            $name = 'unknown';
        }

        return $name;
    }

}
