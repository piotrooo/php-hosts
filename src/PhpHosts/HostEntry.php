<?php
/**
 * Copyright (C) 2013-2020
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

class HostEntry
{
    private string $type;
    private ?string $ip;
    private ?array $names;
    private ?string $comment;

    public static function forIp(string $type, string $ip, array $names): HostEntry
    {
        return new HostEntry($type, $ip, $names, null);
    }

    public static function forComment(string $comment): HostEntry
    {
        return new HostEntry(Type::COMMENT, null, null, $comment);
    }

    public function __construct(string $type, ?string $ip, ?array $names, ?string $comment)
    {
        $this->type = $type;
        $this->ip = $ip;
        $this->names = $names;
        $this->comment = $comment;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function getNames(): ?array
    {
        return $this->names;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function isReal(): bool
    {
        return in_array($this->type, [Type::IPV4, Type::IPV6]);
    }

    public function concat(HostEntry $hostEntry): void
    {
        $this->names = array_merge($this->names, $hostEntry->getNames());
    }

    public function formatForFile(): string
    {
        if ($this->isReal()) {
            $names = implode(' ', $this->names);
            return "{$this->ip}\t{$names}\n";
        }

        return "# {$this->comment}\n";
    }
}
