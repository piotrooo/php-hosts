<?php
declare(strict_types=1);

namespace PhpHosts\Path;

interface PathProvider
{
    public function get(): string;
}
