<?php

namespace Pastell\Service\Document;

class DocumentSize
{
    /**
     * @var string
     */
    private $workspacePath;

    public function __construct(string $workspacePath)
    {
        $this->workspacePath = $workspacePath;
    }

    public function getSize(string $documentId): int
    {
        $size = 0;
        $firstDirectory = $documentId[0];
        $secondDirectory = $documentId[1];

        $directoryPath = $this->workspacePath . "/$firstDirectory/$secondDirectory";
        $files = scandir($directoryPath);
        foreach ($files as $filename) {
            if (fnmatch("$documentId*", $filename)) {
                $size += filesize($this->workspacePath . "/$firstDirectory/$secondDirectory/$filename");
            }
        }

        return $size;
    }

    /**
     * @see https://gist.github.com/liunian/9338301
     */
    public function getHumanReadableSize(int $size, int $precision = 2): string
    {
        $units = ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        $step = 1024;
        $i = 0;
        while (($size / $step) > 0.9) {
            $size /= $step;
            ++$i;
        }
        return round($size, $precision) . $units[$i];
    }
}
