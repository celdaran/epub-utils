<?php namespace App\lib;

class Renamer
{
    /**
     * @param string $fileName
     * @return string
     */
    public function getChapterNumber(string $fileName): string
    {
        $part = explode('_', $fileName);
        if (count($part) > 1) {
            $oldNumber = array_shift($part);
            $newNumber = sprintf('%04d', (int)$oldNumber);
        } else {
            $newNumber = '';
        }

        return $newNumber;
    }

}
