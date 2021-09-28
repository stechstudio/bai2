<?php
namespace STS\Bai2\RecordTypes;

use PHPUnit\Framework\TestCase;

final class FileRecordTypeTest extends TestCase
{

    private static string $headerLine = '01,SENDR1,RECVR1,210616,1700,01,80,10,2/';

    public function testParseLineSetsCorrectRecordCode()
    {
        $fileRecord = new FileRecordType;
        $fileRecord->parseLine(self::$headerLine);

        $this->assertEquals('01', $fileRecord->getRecordCode());
    }

    public function testParseLineSetsCorrectSenderIdentification()
    {
        $fileRecord = new FileRecordType;
        $fileRecord->parseLine(self::$headerLine);

        $this->assertEquals('SENDR1', $fileRecord->getSenderIdentification());
    }

    public function testParseLineSetsCorrectReceiverIdentification()
    {
        $fileRecord = new FileRecordType;
        $fileRecord->parseLine(self::$headerLine);

        $this->assertEquals('RECVR1', $fileRecord->getReceiverIdentification());
    }

    public function testParseLineSetsCorrectFileCreationDate()
    {
        $fileRecord = new FileRecordType;
        $fileRecord->parseLine(self::$headerLine);

        $this->assertEquals('210616', $fileRecord->getFileCreationDate());
    }

    public function testParseLineSetsCorrectFileCreationTime()
    {
        $fileRecord = new FileRecordType;
        $fileRecord->parseLine(self::$headerLine);

        $this->assertEquals('1700', $fileRecord->getFileCreationTime());
    }

    public function testParseLineSetsCorrectFileIdentificationNumber()
    {
        $fileRecord = new FileRecordType;
        $fileRecord->parseLine(self::$headerLine);

        $this->assertEquals('01', $fileRecord->getFileIdentificationNumber());
    }

    public function testParseLineSetsCorrectPhysicalRecordLength()
    {
        $fileRecord = new FileRecordType;
        $fileRecord->parseLine(self::$headerLine);

        $this->assertEquals(80, $fileRecord->getPhysicalRecordLength());
    }

    public function testParseLineSetsCorrectBlockSize()
    {
        $fileRecord = new FileRecordType;
        $fileRecord->parseLine(self::$headerLine);

        $this->assertEquals(10, $fileRecord->getBlockSize());
    }

    public function testParseLineSetsCorrectVersionNumber()
    {
        $fileRecord = new FileRecordType;
        $fileRecord->parseLine(self::$headerLine);

        $this->assertEquals('2', $fileRecord->getVersionNumber());
    }

    public function testParseLineAllowsDefaultedRecordLength()
    {
        $headerLine = '01,SENDR1,RECVR1,210616,1700,01,,10,2/';
        $fileRecord = new FileRecordType;
        $fileRecord->parseLine($headerLine);

        $this->assertNull($fileRecord->getPhysicalRecordLength());
    }

}
