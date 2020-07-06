<?php

namespace PhpHosts;

use Ouzo\Tests\Assert;
use Ouzo\Tests\Mock\Mock;
use PhpHosts\File\FileParser;
use PhpHosts\File\FileWriter;
use PhpHosts\Path\PathProvider;
use PHPUnit\Framework\TestCase;

class HostsTest extends TestCase
{
    private PathProvider $pathProvider;
    private FileParser $fileParser;
    private FileWriter $fileWriter;
    private Hosts $hosts;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pathProvider = Mock::create(PathProvider::class);
        $this->fileParser = Mock::create(FileParser::class);
        $this->fileWriter = Mock::create(FileWriter::class);
        $this->hosts = new Hosts($this->pathProvider, $this->fileParser, $this->fileWriter);
    }

    /**
     * @test
     */
    public function shouldThrowExceptionWhenHostsFileNotExists()
    {
        try {
            //when
            Hosts::import('/not/exists/path/hosts');
        } catch (HostsFileNotExistsException $e) { //then
            $this->assertEquals("Hosts file '/not/exists/path/hosts' not exists.", $e->getMessage());
        }
    }

    /**
     * @test
     */
    public function shouldPopulateEntries()
    {
        //given
        $hostEntries = [
            HostEntry::forIp(Type::IPV4, '127.0.0.1', ['local']),
            HostEntry::forComment('comment'),
            HostEntry::forIp(Type::IPV4, '10.0.0.1', ['example.com'])
        ];
        Mock::when($this->fileParser)->parse(Mock::any())->thenReturn($hostEntries);

        //when
        $hosts = new Hosts($this->pathProvider, $this->fileParser, $this->fileWriter);

        //then
        $this->assertEquals(3, $hosts->count());
        $this->assertEquals($hostEntries, $hosts->entries());
    }

    /**
     * @test
     */
    public function shouldReturnTrueWhenEntryExistsViaNameSearch()
    {
        //given
        $hostEntries = [
            HostEntry::forIp(Type::IPV4, '127.0.0.1', ['local']),
            HostEntry::forIp(Type::IPV4, '10.0.0.1', ['example1.com', 'example2.com'])
        ];
        Mock::when($this->fileParser)->parse(Mock::any())->thenReturn($hostEntries);

        //when
        $existsByName = $this->hosts->existsByName('example2.com');

        //then
        $this->assertTrue($existsByName);
    }

    /**
     * @test
     */
    public function shouldReturnFalseWhenEntryExistsViaNameSearch()
    {
        //given
        $hostEntries = [
            HostEntry::forIp(Type::IPV4, '127.0.0.1', ['local']),
            HostEntry::forIp(Type::IPV4, '10.0.0.1', ['example1.com', 'example2.com'])
        ];
        Mock::when($this->fileParser)->parse(Mock::any())->thenReturn($hostEntries);

        //when
        $existsByName = $this->hosts->existsByName('non-exists.com');

        //then
        $this->assertFalse($existsByName);
    }

    /**
     * @test
     */
    public function shouldReturnTrueWhenEntryExistsViaIpSearch()
    {
        //given
        $hostEntries = [
            HostEntry::forIp(Type::IPV4, '127.0.0.1', ['local']),
            HostEntry::forIp(Type::IPV4, '10.0.0.1', ['example1.com', 'example2.com'])
        ];
        Mock::when($this->fileParser)->parse(Mock::any())->thenReturn($hostEntries);

        //when
        $existsByIp = $this->hosts->existsByIp('127.0.0.1');

        //then
        $this->assertTrue($existsByIp);
    }

    /**
     * @test
     */
    public function shouldReturnFalseWhenEntryExistsViaIpSearch()
    {
        //given
        $hostEntries = [
            HostEntry::forIp(Type::IPV4, '127.0.0.1', ['local']),
            HostEntry::forIp(Type::IPV4, '10.0.0.1', ['example1.com', 'example2.com'])
        ];
        Mock::when($this->fileParser)->parse(Mock::any())->thenReturn($hostEntries);

        //when
        $existsByIp = $this->hosts->existsByIp('192.168.1.1');

        //then
        $this->assertFalse($existsByIp);
    }

    /**
     * @test
     */
    public function shouldReturnHostEntryWhenSearchByName()
    {
        //given
        $hostEntry = HostEntry::forIp(Type::IPV4, '10.0.0.1', ['example1.com', 'example2.com']);
        $hostEntries = [
            HostEntry::forIp(Type::IPV4, '127.0.0.1', ['local']),
            $hostEntry
        ];
        Mock::when($this->fileParser)->parse(Mock::any())->thenReturn($hostEntries);

        //when
        $findHostEntry = $this->hosts->findByName('example1.com');

        //then
        $this->assertEquals($hostEntry, $findHostEntry);
    }

    /**
     * @test
     */
    public function shouldReturnNullWhenSearchByNameNotExists()
    {
        //given
        $hostEntries = [
            HostEntry::forIp(Type::IPV4, '127.0.0.1', ['local']),
            HostEntry::forIp(Type::IPV4, '10.0.0.1', ['example1.com', 'example2.com'])
        ];
        Mock::when($this->fileParser)->parse(Mock::any())->thenReturn($hostEntries);

        //when
        $findHostEntry = $this->hosts->findByName('non-exists.com');

        //then
        $this->assertNull($findHostEntry);
    }

    /**
     * @test
     */
    public function shouldReturnHostEntryWhenSearchByIp()
    {
        //given
        $hostEntry = HostEntry::forIp(Type::IPV4, '10.0.0.1', ['example1.com', 'example2.com']);
        $hostEntries = [
            HostEntry::forIp(Type::IPV4, '127.0.0.1', ['local']),
            $hostEntry
        ];
        Mock::when($this->fileParser)->parse(Mock::any())->thenReturn($hostEntries);

        //when
        $findHostEntry = $this->hosts->findByIp('10.0.0.1');

        //then
        $this->assertEquals($hostEntry, $findHostEntry);
    }

    /**
     * @test
     */
    public function shouldReturnNullWhenSearchByIpNotExists()
    {
        //given
        $hostEntries = [
            HostEntry::forIp(Type::IPV4, '127.0.0.1', ['local']),
            HostEntry::forIp(Type::IPV4, '10.0.0.1', ['example1.com', 'example2.com'])
        ];
        Mock::when($this->fileParser)->parse(Mock::any())->thenReturn($hostEntries);

        //when
        $findHostEntry = $this->hosts->findByIp('192.168.1.1');

        //then
        $this->assertNull($findHostEntry);
    }

    /**
     * @test
     */
    public function shouldAddHostEntry()
    {
        //given
        $hostEntries = [
            HostEntry::forIp(Type::IPV4, '127.0.0.1', ['local']),
            HostEntry::forIp(Type::IPV4, '10.0.0.1', ['example1.com', 'example2.com'])
        ];
        Mock::when($this->fileParser)->parse(Mock::any())->thenReturn($hostEntries);

        $hostEntry = HostEntry::forIp(Type::IPV4, '192.168.1.1', []);

        //when
        $this->hosts->add($hostEntry);

        //then
        $expected = array_merge($hostEntries, [$hostEntry]);
        $this->assertEquals(3, $this->hosts->count());
        $this->assertEquals($expected, $this->hosts->entries());
    }

    /**
     * @test
     */
    public function shouldMergeHostEntryWhenIpIsTheSame()
    {
        //given
        $hostEntries = [
            HostEntry::forIp(Type::IPV4, '127.0.0.1', ['local']),
            HostEntry::forIp(Type::IPV4, '10.0.0.1', ['example1.com', 'example2.com'])
        ];
        Mock::when($this->fileParser)->parse(Mock::any())->thenReturn($hostEntries);

        $hostEntry = HostEntry::forIp(Type::IPV4, '127.0.0.1', ['new-name.example.com', 'second-new-name.example.com']);

        //when
        $this->hosts->add($hostEntry);

        //then
        $this->assertEquals(2, $this->hosts->count());
        $this->assertEquals(['local', 'new-name.example.com', 'second-new-name.example.com'], $this->hosts->findByIp('127.0.0.1')->getNames());
    }

    /**
     * @test
     */
    public function shouldAddAllHostEntries()
    {
        //given
        $hostEntries = [
            HostEntry::forIp(Type::IPV4, '127.0.0.1', ['local']),
            HostEntry::forIp(Type::IPV4, '10.0.0.1', ['example1.com', 'example2.com'])
        ];

        //when
        $this->hosts->addAll($hostEntries);

        //then
        $this->assertEquals(2, $this->hosts->count());
        Assert::thatArray($this->hosts->entries())
            ->extracting('getType()', 'getIp()', 'getNames()', 'getComment()')
            ->containsExactly(
                [Type::IPV4, '127.0.0.1', ['local'], null],
                [Type::IPV4, '10.0.0.1', ['example1.com', 'example2.com'], null]
            );
    }

    /**
     * @test
     */
    public function shouldRemoveByIp()
    {
        //given
        $hostEntries = [
            HostEntry::forIp(Type::IPV4, '127.0.0.1', ['local']),
            HostEntry::forIp(Type::IPV4, '10.0.0.1', ['example1.com', 'example2.com'])
        ];
        Mock::when($this->fileParser)->parse(Mock::any())->thenReturn($hostEntries);

        //when
        $this->hosts->removeByIp('10.0.0.1');

        //then
        $this->assertEquals(1, $this->hosts->count());
        Assert::thatArray($this->hosts->entries())
            ->extracting('getType()', 'getIp()', 'getNames()', 'getComment()')
            ->containsExactly(
                [Type::IPV4, '127.0.0.1', ['local'], null]
            );
    }

    /**
     * @test
     */
    public function shouldRemoveByName()
    {
        //given
        $hostEntries = [
            HostEntry::forIp(Type::IPV4, '127.0.0.1', ['local']),
            HostEntry::forIp(Type::IPV4, '10.0.0.1', ['example1.com', 'example2.com'])
        ];
        Mock::when($this->fileParser)->parse(Mock::any())->thenReturn($hostEntries);

        //when
        $this->hosts->removeByName('example2.com');

        //then
        $this->assertEquals(1, $this->hosts->count());
        Assert::thatArray($this->hosts->entries())
            ->extracting('getType()', 'getIp()', 'getNames()', 'getComment()')
            ->containsExactly(
                [Type::IPV4, '127.0.0.1', ['local'], null]
            );
    }

    /**
     * @test
     */
    public function shouldWriteToFile()
    {
        //given
        $hostEntries = [
            HostEntry::forIp(Type::IPV4, '127.0.0.1', ['local']),
            HostEntry::forComment('some fancy comment'),
            HostEntry::forIp(Type::IPV4, '10.0.0.1', ['example1.com', 'example2.com'])
        ];
        Mock::when($this->fileParser)->parse(Mock::any())->thenReturn($hostEntries);
        Mock::when($this->pathProvider)->get()->thenReturn('/path/to/hosts');

        //when
        $this->hosts->write();

        //then
        Mock::verify($this->fileWriter)->write('/path/to/hosts', $hostEntries);
    }
}
