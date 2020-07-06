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

namespace PhpHosts;

use Ouzo\Utilities\Arrays;
use Ouzo\Utilities\FluentFunctions;
use PhpHosts\File\FileParser;
use PhpHosts\File\FileWriter;
use PhpHosts\Path\FixedPathProvider;
use PhpHosts\Path\OsBasedPathProvider;
use PhpHosts\Path\PathProvider;

class Hosts
{
    private PathProvider $pathProvider;
    private FileParser $fileParser;
    private FileWriter $fileWriter;

    /** @var HostEntry[] */
    private array $hostEntries;

    public static function initialize(): Hosts
    {
        return new Hosts(new OsBasedPathProvider(), new FileParser(), new FileWriter());
    }

    public static function import(string $path): Hosts
    {
        if (!file_exists($path)) {
            throw new HostsFileNotExistsException("Hosts file '{$path}' not exists.");
        }

        return new Hosts(new FixedPathProvider($path), new FileParser(), new FileWriter());
    }

    public function __construct(PathProvider $pathProvider, FileParser $fileParser, FileWriter $fileWriter)
    {
        $this->pathProvider = $pathProvider;
        $this->fileParser = $fileParser;
        $this->fileWriter = $fileWriter;

        $this->populate();
    }

    public function count(): int
    {
        return count($this->hostEntries);
    }

    /**
     * @return HostEntry[]
     */
    public function entries(): array
    {
        return $this->hostEntries;
    }

    public function existsByName(string $name): bool
    {
        return Arrays::any($this->hostEntries, function (HostEntry $hostEntry) use ($name) {
            return in_array($name, $hostEntry->getNames());
        });
    }

    public function existsByIp(string $ip): bool
    {
        return Arrays::any($this->hostEntries, function (HostEntry $hostEntry) use ($ip) {
            return $ip === $hostEntry->getIp();
        });
//        return Arrays::any($this->hostEntries, FluentFunctions::extractExpression('getIp()')->equals($ip));
    }

    public function findByName(string $name): ?HostEntry
    {
        return Arrays::find($this->hostEntries, function (HostEntry $hostEntry) use ($name) {
            return in_array($name, $hostEntry->getNames());
        });
    }

    public function findByIp(string $ip): ?HostEntry
    {
        return Arrays::find($this->hostEntries, FluentFunctions::extractExpression('getIp()')->equals($ip));
    }

    public function add(HostEntry $hostEntry): void
    {
        $existingHostEntry = $this->findByIp($hostEntry->getIp());
        if (is_null($existingHostEntry)) {
            $this->hostEntries[] = $hostEntry;
        } else {
            $existingHostEntry->concat($hostEntry);
        }
    }

    /**
     * @param HostEntry[] $hostEntries
     */
    public function addAll(array $hostEntries): void
    {
        foreach ($hostEntries as $hostEntry) {
            $this->add($hostEntry);
        }
    }

    public function removeByIp(string $ip): void
    {
        $existingHostEntry = $this->findByIp($ip);
        $this->removeEntry($existingHostEntry);
    }

    public function removeByName(string $name): void
    {
        $existingHostEntry = $this->findByName($name);
        $this->removeEntry($existingHostEntry);
    }

    public function write(): void
    {
        $this->fileWriter->write($this->pathProvider->get(), $this->hostEntries);
    }

    public function populate(): void
    {
        $this->hostEntries = $this->fileParser->parse($this->pathProvider->get());
    }

    private function removeEntry(?HostEntry $existingHostEntry): void
    {
        if (!is_null($existingHostEntry)) {
            $this->hostEntries = Arrays::filter($this->hostEntries, function (HostEntry $hostEntry) use ($existingHostEntry) {
                return $hostEntry->isReal() &&
                    $existingHostEntry->isReal() &&
                    $hostEntry->getIp() != $existingHostEntry->getIp();
            });
        }
    }
}
