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

    // -- test field getters ---------------------------------------------------

    /**
     * @dataProvider inputLinesProducer
     */
    public function testGetSenderIdentification(array $inputLines): void
    {
        $this->withRecord($inputLines, function ($fileRecord) {
            $this->assertEquals('SENDR1', $fileRecord->getSenderIdentification());
        });
    }

    // TODO(zmd): public function testGetSenderIdentifiationMissing(): void {}

    /**
     * @dataProvider inputLinesProducer
     */
    public function testGetReceiverIdentification(array $inputLines): void
    {
        $this->withRecord($inputLines, function ($fileRecord) {
            $this->assertEquals('RECVR1', $fileRecord->getReceiverIdentification());
        });
    }

    // TODO(zmd): public function testGetReceiverIdentifiationMissing(): void {}

    /**
     * @dataProvider inputLinesProducer
     */
    public function testGetFileCreationDate(array $inputLines): void
    {
        $this->withRecord($inputLines, function ($fileRecord) {
            $this->assertEquals('210616', $fileRecord->getFileCreationDate());
        });
    }

    // TODO(zmd): public function testGetFileCreationDateMissing(): void {}

    /**
     * @dataProvider inputLinesProducer
     */
    public function testGetFileCreationTime(array $inputLines): void
    {
        $this->withRecord($inputLines, function ($fileRecord) {
            $this->assertEquals('1700', $fileRecord->getFileCreationTime());
        });
    }

    // TODO(zmd): public function testGetFileCreationTimeMissing(): void {}

    /**
     * @dataProvider inputLinesProducer
     */
    public function testGetFileIdentificationNumber(array $inputLines): void
    {
        $this->withRecord($inputLines, function ($fileRecord) {
            $this->assertEquals('01', $fileRecord->getFileIdentificationNumber());
        });
    }

    // TODO(zmd): public function testGetFileIdentificationNumberMissing(): void {}

    /**
     * @dataProvider inputLinesProducer
     */
    public function testGetPhysicalRecordLength(array $inputLines): void
    {
        $this->withRecord($inputLines, function ($fileRecord) {
            $this->assertEquals(80, $fileRecord->getPhysicalRecordLength());
        });
    }

    // TODO(zmd): public function testGetPhysicalRecordLengthDefaulted(): void {}

    /**
     * @dataProvider inputLinesProducer
     */
    public function testGetBlockSize(array $inputLines): void
    {
        $this->withRecord($inputLines, function ($fileRecord) {
            $this->assertEquals(10, $fileRecord->getBlockSize());
        });
    }

    // TODO(zmd): public function testGetBlockSizeDefaulted(): void {}

    /**
     * @dataProvider inputLinesProducer
     */
    public function testGetVersionNumber(array $inputLines): void
    {
        $this->withRecord($inputLines, function ($fileRecord) {
            $this->assertEquals('2', $fileRecord->getVersionNumber());
        });
    }

    // TODO(zmd): public function testGetVersionNumberMissing(): void {}

    /**
     * @dataProvider inputLinesProducer
     */
    public function testGetFileControlTotal(array $inputLines): void
    {
        $this->withRecord($inputLines, function ($fileRecord) {
            $this->assertEquals(1337, $fileRecord->getFileControlTotal());
        });
    }

    // TODO(zmd): public function testGetFileControlTotalMissing(): void {}

    /**
     * @dataProvider inputLinesProducer
     */
    public function testGetNumberOfGroups(array $inputLines): void
    {
        $this->withRecord($inputLines, function ($fileRecord) {
            $this->assertEquals(2, $fileRecord->getNumberOfGroups());
        });
    }

    // TODO(zmd): public function testGetNumberOfGroupsMissing(): void {}

    /**
     * @dataProvider inputLinesProducer
     */
    public function testGetNumberOfRecords(array $inputLines): void
    {
        $this->withRecord($inputLines, function ($fileRecord) {
            $this->assertEquals(42, $fileRecord->getNumberOfRecords());
        });
    }

    // TODO(zmd): public function testGetNumberOfRecordsMissing(): void {}

    /**
     * @dataProvider inputLinesProducer
     */
    public function testGetGroups(array $inputLines): void
    {
        $this->withRecord($inputLines, function ($fileRecord) {
            $this->assertEquals(2, count($fileRecord->getGroups()));
        });
    }

    // -- test overall functionality -------------------------------------------

    // TODO(zmd): test ::toArray()

    // -- test overall error handling ------------------------------------------

    // TODO(zmd): test when input lines exceed length of physicalRecordLength

    // TODO(zmd): test field access when header line never encountered

    // TODO(zmd): test field access when trailer line never encountered

    // TODO(zmd): test when continuation encountered before other kind of line

    // TODO(zmd): test when child-destined line encountered before file header

    // TODO(zmd): test when header malformed (e.g. missing field)

    // TODO(zmd): test when trailer malformed (e.g. missing field)

    // TODO(zmd): test when direct child malformed (e.g. missing field)

}
