<?php
declare(strict_types=1);

namespace PhpHosts\Path;

use Ouzo\Utilities\Strings;

class OsBasedPathProvider implements PathProvider
{
    public function get(): string
    {
        if (Strings::containsIgnoreCase(PHP_OS, 'win')) {
            return 'c:\windows\system32\drivers\etc\hosts';
        }

        return '/etc/hosts';
    }
}
