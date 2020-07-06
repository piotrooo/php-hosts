<?php
declare(strict_types=1);

namespace PhpHosts\File;

use PhpHosts\HostEntry;

class FileWriter
{
    /**
     * @param HostEntry[] $hostEntries
     */
    public function write(string $path, array $hostEntries): void
    {
        $content = '';
        foreach ($hostEntries as $hostEntry) {
            $content .= $hostEntry->formatForFile();
        }
        file_put_contents($path, $content);
    }
}
