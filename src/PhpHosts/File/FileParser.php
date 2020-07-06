<?php
/**
 * Copyright (C) 2020
 * Piotr Olaszewski <piotroo89@gmail.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */
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
