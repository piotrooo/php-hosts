<?php

namespace PhpHosts\Path;

use PHPUnit\Framework\TestCase;

class FixedPathProviderTest extends TestCase
{
    /**
     * @test
     */
    public function shouldReturnPassedPath()
    {
        //given
        $fixedPathProvider = new FixedPathProvider('/path/to/hosts');

        //when
        $path = $fixedPathProvider->get();

        //then
        $this->assertEquals('/path/to/hosts', $path);
    }
}
