<?php
declare(strict_types=1);

namespace PhpHosts\File;

use Ouzo\Utilities\Arrays;
use Ouzo\Utilities\Strings;
use PhpHosts\HostEntry;
use PhpHosts\Type;

class FileParser
{
    private const COMMENT_START_CHAR = '#';

    /** @var HostEntry[] */
    private array $hostEntries = [];

    /**
     * @return HostEntry[]
     */
    public function parse(string $path): array
    {
        $content = file_get_contents($path);

        $this->hostEntries = [];

        $lines = preg_split('/$\R?^/m', $content);
        foreach ($lines as $line) {
            if (Strings::isBlank($line)) {
                continue;
            }

            $elements = preg_split('/\s+/', $line, -1, PREG_SPLIT_NO_EMPTY);
            $hostEntry = $this->getEntry($elements);

            $existingHostEntry = $this->getMatchingHostEntry($hostEntry);
            if (is_null($existingHostEntry)) {
                $this->hostEntries[] = $hostEntry;
            } else {
                $existingHostEntry->concat($hostEntry);
            }
        }

        return $this->hostEntries;
    }

    private function getEntry(array $elements): HostEntry
    {
        $first = $elements[0];
        if (Strings::startsWith($first, self::COMMENT_START_CHAR)) {
            $comment = trim(ltrim(implode(' ', $elements), self::COMMENT_START_CHAR));
            return HostEntry::forComment($comment);
        }

        $type = null;
        if (filter_var($first, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $type = Type::IPV4;
        } else if (filter_var($first, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $type = Type::IPV6;
        }
        $names = array_slice($elements, 1);
        return HostEntry::forIp($type, $first, $names);
    }

    private function getMatchingHostEntry(HostEntry $newHostEntry): ?HostEntry
    {
        return Arrays::find($this->hostEntries, function (HostEntry $hostEntry) use ($newHostEntry) {
            return $hostEntry->isReal() &&
                $newHostEntry->isReal() &&
                $hostEntry->getIp() === $newHostEntry->getIp();
        });
    }
}
