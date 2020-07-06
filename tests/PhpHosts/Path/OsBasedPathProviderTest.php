<?php

namespace PhpHosts\Path;

use PHPUnit\Framework\TestCase;

class OsBasedPathProviderTest extends TestCase
{
    /**
     * @test
     */
    public function shouldResolveHostsPathForLinux()
    {
        //given
        $osBasedPathProvider = new OsBasedPathProvider();

        //when
        $path = $osBasedPathProvider->get();

        //then
        $this->assertEquals('/etc/hosts', $path);
    }
}
