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

namespace PhpHosts\File;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamFile;
use Ouzo\Tests\Assert;
use PhpHosts\Type;
use PHPUnit\Framework\TestCase;

class FileParserTest extends TestCase
{
    private FileParser $fileParser;
    private vfsStreamDirectory $root;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fileParser = new FileParser();
        $this->root = vfsStream::setup();
    }

    /**
     * @test
     */
    public function shouldParseSingleEntryWithTwoNames()
    {
        //given
        $hosts = "82.132.132.132\texample.com example\n";
        $file = $this->file($hosts);

        //when
        $hostEntries = $this->fileParser->parse($file->url());

        //then
        Assert::thatArray($hostEntries)
            ->extracting('getType()', 'getIp()', 'getNames()')
            ->containsExactly([Type::IPV4, '82.132.132.132', ['example.com', 'example']]);
    }

    /**
     * @test
     */
    public function shouldParseWithComment()
    {
        //given
        $hosts = "127.0.0.1 example1.com example2.com\n#existing comment";
        $file = $this->file($hosts);

        //when
        $hostEntries = $this->fileParser->parse($file->url());

        //then
        Assert::thatArray($hostEntries)
            ->extracting('getType()', 'getIp()', 'getNames()', 'getComment()')
            ->containsExactly(
                [Type::IPV4, '127.0.0.1', ['example1.com', 'example2.com'], null],
                [Type::COMMENT, null, null, 'existing comment']
            );
    }

    /**
     * @test
     */
    public function shouldParseWithCommentBetweenHosts()
    {
        //given
        $hosts = "127.0.0.1 example1.com example2.com\n# this is a comment\n\n3.4.5.6 random.com example2.com\n";
        $file = $this->file($hosts);

        //when
        $hostEntries = $this->fileParser->parse($file->url());

        //then
        Assert::thatArray($hostEntries)
            ->extracting('getType()', 'getIp()', 'getNames()', 'getComment()')
            ->containsExactly(
                [Type::IPV4, '127.0.0.1', ['example1.com', 'example2.com'], null],
                [Type::COMMENT, null, null, 'this is a comment'],
                [Type::IPV4, '3.4.5.6', ['random.com', 'example2.com'], null]
            );
    }

    /**
     * @test
     */
    public function shouldMergeTheSameIps()
    {
        //given
        $hosts = "127.0.0.1 example1.com\n127.0.0.1 example2.com\n";
        $file = $this->file($hosts);

        //when
        $hostEntries = $this->fileParser->parse($file->url());

        //then
        Assert::thatArray($hostEntries)
            ->extracting('getType()', 'getIp()', 'getNames()', 'getComment()')
            ->containsExactly(
                [Type::IPV4, '127.0.0.1', ['example1.com', 'example2.com'], null]
            );
    }

    /**
     * @test
     */
    public function shouldParseMultiComments()
    {
        //given
        $hosts = "# the first comment\n127.0.0.1 example1.com example2.com\n#the second comment\n";
        $file = $this->file($hosts);

        //when
        $hostEntries = $this->fileParser->parse($file->url());

        //then
        Assert::thatArray($hostEntries)
            ->extracting('getType()', 'getIp()', 'getNames()', 'getComment()')
            ->containsExactly(
                [Type::COMMENT, null, null, 'the first comment'],
                [Type::IPV4, '127.0.0.1', ['example1.com', 'example2.com'], null],
                [Type::COMMENT, null, null, 'the second comment']
            );
    }

    private function file(string $hosts): vfsStreamFile
    {
        return vfsStream::newFile('hosts')
            ->setContent($hosts)
            ->at($this->root);
    }
}
