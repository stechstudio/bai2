<?php

namespace STS\Bai2\Records;

use PHPUnit\Framework\TestCase;

final class FileRecordTest extends TestCase
{

    public function inputLinesProducer(): array
    {
        return [
            [[
                '01,SENDR1,RECVR1,210616,1700,01,80,10,2/',
                '02,abc,def,1,212209,,,/',
                '98,10000,0,1/',
                '02,uvw,xyz,1,212209,,,/',
                '98,5000,0,1/',
                '99,1337,2,42/',
            ]],
            [[
                '01,SENDR1,RECVR1/',
                '88,210616,1700,01,80,10,2/',
                '02,abc,def/',
                '88,1,212209,,,/',
                '98,10000/',
                '88,0,1/',
                '02,uvw/',
                '88,xyz,1,212209,,,/',
                '98,5000,0,1/',
                '99,1337/',
                '88,2,42/',
            ]],
        ];
    }

    protected function withRecord(array $input, callable $callable): void
    {
        $record = new FileRecord();

        foreach ($input as $line) {
            $record->parseLine($line);
        }

        $callable($record);
    }

    /**
     * @dataProvider inputLinesProducer
     */
    public function testGetSenderIdentification(array $inputLines): void
    {
        $this->withRecord($inputLines, function ($fileRecord) {
            $this->assertEquals('SENDR1', $fileRecord->getSenderIdentification());
        });
    }

    /**
     * @dataProvider inputLinesProducer
     */
    public function testGetReceiverIdentification(array $inputLines): void
    {
        $this->withRecord($inputLines, function ($fileRecord) {
            $this->assertEquals('RECVR1', $fileRecord->getReceiverIdentification());
        });
    }

    /**
     * @dataProvider inputLinesProducer
     */
    public function testGetFileCreationDate(array $inputLines): void
    {
        $this->withRecord($inputLines, function ($fileRecord) {
            $this->assertEquals('210616', $fileRecord->getFileCreationDate());
        });
    }

    /**
     * @dataProvider inputLinesProducer
     */
    public function testGetFileCreationTime(array $inputLines): void
    {
        $this->withRecord($inputLines, function ($fileRecord) {
            $this->assertEquals('1700', $fileRecord->getFileCreationTime());
        });
    }

    /**
     * @dataProvider inputLinesProducer
     */
    public function testGetFileIdentificationNumber(array $inputLines): void
    {
        $this->withRecord($inputLines, function ($fileRecord) {
            $this->assertEquals('01', $fileRecord->getFileIdentificationNumber());
        });
    }

    /**
     * @dataProvider inputLinesProducer
     */
    public function testGetPhysicalRecordLength(array $inputLines): void
    {
        $this->withRecord($inputLines, function ($fileRecord) {
            $this->assertEquals(80, $fileRecord->getPhysicalRecordLength());
        });
    }

    /**
     * @dataProvider inputLinesProducer
     */
    public function testGetBlockSize(array $inputLines): void
    {
        $this->withRecord($inputLines, function ($fileRecord) {
            $this->assertEquals(10, $fileRecord->getBlockSize());
        });
    }

    /**
     * @dataProvider inputLinesProducer
     */
    public function testGetVersionNumber(array $inputLines): void
    {
        $this->withRecord($inputLines, function ($fileRecord) {
            $this->assertEquals('2', $fileRecord->getVersionNumber());
        });
    }

    /**
     * @dataProvider inputLinesProducer
     */
    public function testGetFileControlTotal(array $inputLines): void
    {
        $this->withRecord($inputLines, function ($fileRecord) {
            $this->assertEquals(1337, $fileRecord->getFileControlTotal());
        });
    }

    /**
     * @dataProvider inputLinesProducer
     */
    public function testGetNumberOfGroups(array $inputLines): void
    {
        $this->withRecord($inputLines, function ($fileRecord) {
            $this->assertEquals(2, $fileRecord->getNumberOfGroups());
        });
    }

    /**
     * @dataProvider inputLinesProducer
     */
    public function testGetNumberOfRecords(array $inputLines): void
    {
        $this->withRecord($inputLines, function ($fileRecord) {
            $this->assertEquals(42, $fileRecord->getNumberOfRecords());
        });
    }

}
