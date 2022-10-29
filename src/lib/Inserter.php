<?php namespace App\lib;

class Inserter
{
    private string $dirName;
    private string $fileName;
    private Renamer $renamer;

    /**
     * @param string $dirName
     * @param $fileName
     */
    public function __construct(string $dirName, $fileName)
    {
        $this->dirName = rtrim($dirName, '/');
        $this->fileName = $fileName;
        $this->renamer = new Renamer();
    }

    /**
     * No idea what this is going to do or how it will work
     * Going to start with "giant block of code" and see what happens
     */
    public function exec(string $delimiter)
    {
        // Get css files
        $files = scandir($this->dirName);

        // Extract current counts
        $filteredFiles = [];
        foreach ($files as $file) {
            // Ignore non XHTML files
            if (substr($file, -6) !== '.xhtml') {
                continue;
            }
            // Log name and number of each hit found
            if (preg_match('/[0-9]{4}/', $file)) {
                $filteredFiles[] = [
                    'number' => (int)$this->renamer->getChapterNumber($file, $delimiter),
                    'name' => $file,
                ];
            }
        }

        // Get chapter number of file to be inserted
        $newChapterNumber = (int)$this->renamer->getChapterNumber($this->fileName, $delimiter);
        $newChapterName = $this->renamer->getChapterName($this->fileName, $delimiter);

        // Now go for it...
        $fileCreated = false;
        foreach ($filteredFiles as $file) {

            // Skip files that don't need to be updated
            if ($file['number'] < $newChapterNumber) {
                continue;
            }

            // If we've hit this part, it's time to insert (one time only)
            if (!$fileCreated) {
                $newFileName = sprintf('%s/%04d%s%s.xhtml',
                    $this->dirName, $newChapterNumber, $delimiter, $newChapterName);
                touch($newFileName);
                $fileCreated = true;
            }

            $oldChapterName = $this->renamer->getChapterName($file['name'], $delimiter);

            $oldFileName = sprintf('%s/%s', $this->dirName, $file['name']);
            $newFileName = sprintf('%s/%04d%s%s.xhtml',
                $this->dirName, $file['number'] + 1, $delimiter, $oldChapterName);

            rename($oldFileName, $newFileName);
        }
    }

}
