<?php

namespace STS\Bai2\Records;

use PHPUnit\Framework\TestCase;

final class FileRecordTest extends TestCase
{

    private static string $headerLine = '01,SENDR1,RECVR1,210616,1700,01,80,10,2/';

    private static string $partialHeaderLine = '01,SENDR1,RECVR1,210616/';

    private static string $partialHeaderContinuationLine = '88,1700,01,80,10,2/';

    private static string $trailerLine = '99,1337,1,42/';

    private static string $partialTrailerLine = '99,1337/';

    private static string $partialTrailerContinuationLine = '88,1,42/';

    public function testParseLineSetsCorrectSenderIdentification()
    {
        $fileRecord = new FileRecord();
        $fileRecord->parseLine(self::$headerLine);

        $this->assertEquals('SENDR1', $fileRecord->getSenderIdentification());
    }

    public function testParseLineSetsCorrectReceiverIdentification()
    {
        $fileRecord = new FileRecord();
        $fileRecord->parseLine(self::$headerLine);

        $this->assertEquals('RECVR1', $fileRecord->getReceiverIdentification());
    }

    public function testParseLineSetsCorrectFileCreationDate()
    {
        $fileRecord = new FileRecord();
        $fileRecord->parseLine(self::$headerLine);

        $this->assertEquals('210616', $fileRecord->getFileCreationDate());
    }

    public function testParseLineSetsCorrectFileCreationTime()
    {
        $fileRecord = new FileRecord();
        $fileRecord->parseLine(self::$headerLine);

        $this->assertEquals('1700', $fileRecord->getFileCreationTime());
    }

    public function testParseLineSetsCorrectFileIdentificationNumber()
    {
        $fileRecord = new FileRecord();
        $fileRecord->parseLine(self::$headerLine);

        $this->assertEquals('01', $fileRecord->getFileIdentificationNumber());
    }

    public function testParseLineSetsCorrectPhysicalRecordLength()
    {
        $fileRecord = new FileRecord();
        $fileRecord->parseLine(self::$headerLine);

        $this->assertEquals(80, $fileRecord->getPhysicalRecordLength());
    }

    public function testParseLineSetsCorrectBlockSize()
    {
        $fileRecord = new FileRecord();
        $fileRecord->parseLine(self::$headerLine);

        $this->assertEquals(10, $fileRecord->getBlockSize());
    }

    public function testParseLineSetsCorrectVersionNumber()
    {
        $fileRecord = new FileRecord();
        $fileRecord->parseLine(self::$headerLine);

        $this->assertEquals('2', $fileRecord->getVersionNumber());
    }

    public function testParseLineAllowsDefaultedPhysicalRecordLength()
    {
        $headerLine = '01,SENDR1,RECVR1,210616,1700,01,,10,2/';
        $fileRecord = new FileRecord();
        $fileRecord->parseLine($headerLine);

        $this->assertNull($fileRecord->getPhysicalRecordLength());
    }

    public function testParseLineAllowsDefaultedBlockSize()
    {
        $headerLine = '01,SENDR1,RECVR1,210616,1700,01,80,,2/';
        $fileRecord = new FileRecord();
        $fileRecord->parseLine($headerLine);

        $this->assertNull($fileRecord->getBlockSize());
    }

    public function testParseLineSetsCorrectFileControlTotal()
    {
        $fileRecord = new FileRecord();
        $fileRecord->parseLine(self::$trailerLine);

        $this->assertEquals(1337, $fileRecord->getFileControlTotal());
    }

    public function testParseLineSetsCorrectNumberOfGroups()
    {
        $fileRecord = new FileRecord();
        $fileRecord->parseLine(self::$trailerLine);

        $this->assertEquals(1, $fileRecord->getNumberOfGroups());
    }

    public function testParseLineSetsCorrectNumberOfRecords()
    {
        $fileRecord = new FileRecord();
        $fileRecord->parseLine(self::$trailerLine);

        $this->assertEquals(42, $fileRecord->getNumberOfRecords());
    }

    public function testParseLineCanHandleAPartialHeaderContinuationRecord()
    {
        $fileRecord = new FileRecord();
        $fileRecord->parseLine(self::$partialHeaderLine);
        $fileRecord->parseLine(self::$partialHeaderContinuationLine);

        $this->assertEquals('SENDR1', $fileRecord->getSenderIdentification());
        $this->assertEquals('RECVR1', $fileRecord->getReceiverIdentification());
        $this->assertEquals('210616', $fileRecord->getFileCreationDate());
        $this->assertEquals('1700', $fileRecord->getFileCreationTime());
        $this->assertEquals('01', $fileRecord->getFileIdentificationNumber());
        $this->assertEquals(80, $fileRecord->getPhysicalRecordLength());
        $this->assertEquals(10, $fileRecord->getBlockSize());
        $this->assertEquals('2', $fileRecord->getVersionNumber());
    }

}
