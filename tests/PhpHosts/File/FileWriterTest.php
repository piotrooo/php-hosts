<?php

namespace PhpHosts\File;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamFile;
use PhpHosts\HostEntry;
use PhpHosts\Type;
use PHPUnit\Framework\TestCase;

class FileWriterTest extends TestCase
{
    private FileWriter $fileWriter;
    private vfsStreamDirectory $root;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fileWriter = new FileWriter();
        $this->root = vfsStream::setup();
    }

    /**
     * @test
     */
    public function shouldSaveHostEntriesInFile()
    {
        //given
        $file = $this->file();
        $hostEntries = [
            HostEntry::forIp(Type::IPV4, '127.0.0.1', ['local']),
            HostEntry::forComment('some fancy comment'),
            HostEntry::forIp(Type::IPV4, '10.0.0.1', ['example1.com', 'example2.com'])
        ];

        //when
        $this->fileWriter->write($file->url(), $hostEntries);

        //then
        $this->assertStringContainsString("127.0.0.1\tlocal", $file->getContent());
        $this->assertStringContainsString("# some fancy comment", $file->getContent());
        $this->assertStringContainsString("10.0.0.1\texample1.com example2.com", $file->getContent());
    }

    private function file(): vfsStreamFile
    {
        return vfsStream::newFile('hosts')
            ->at($this->root);
    }
}
