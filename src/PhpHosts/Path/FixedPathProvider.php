<?php
declare(strict_types=1);

namespace PhpHosts\Path;

class FixedPathProvider implements PathProvider
{
    private string $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function get(): string
    {
        return $this->path;
    }
}
