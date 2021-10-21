<?php

namespace STS\Bai2\Parsers;

use PHPUnit\Framework\TestCase;

final class FileHeaderParserTest extends TestCase
{

    private static string $headerLine = '01,SENDR1,RECVR1,210616,1700,01,80,10,2/';

    private static string $headerLinePartialFirst = '01,SENDR1,RECVR1/';

    private static string $headerLinePartialContinued = '88,210616,1700,01,80,10,2/';

    public function testParseFromSingleLine(): void
    {
        $parser = new FileHeaderParser();
        $parser->push(self::$headerLine);

        $this->assertEquals('01', $parser->offsetGet('recordCode'));
        $this->assertEquals('SENDR1', $parser->offsetGet('senderIdentification'));
        $this->assertEquals('RECVR1', $parser->offsetGet('receiverIdentification'));
        $this->assertEquals('210616', $parser->offsetGet('fileCreationDate'));
        $this->assertEquals('1700', $parser->offsetGet('fileCreationTime'));
        $this->assertEquals('01', $parser->offsetGet('fileIdentificationNumber'));
        $this->assertEquals(80, $parser->offsetGet('physicalRecordLength'));
        $this->assertEquals(10, $parser->offsetGet('blockSize'));
        $this->assertEquals('2', $parser->offsetGet('versionNumber'));
    }

    public function testParseFromMultipleLines(): void
    {
        $parser = new FileHeaderParser();
        $parser->push(self::$headerLinePartialFirst);
        $parser->push(self::$headerLinePartialContinued);

        $this->assertEquals('01', $parser->offsetGet('recordCode'));
        $this->assertEquals('SENDR1', $parser->offsetGet('senderIdentification'));
        $this->assertEquals('RECVR1', $parser->offsetGet('receiverIdentification'));
        $this->assertEquals('210616', $parser->offsetGet('fileCreationDate'));
        $this->assertEquals('1700', $parser->offsetGet('fileCreationTime'));
        $this->assertEquals('01', $parser->offsetGet('fileIdentificationNumber'));
        $this->assertEquals(80, $parser->offsetGet('physicalRecordLength'));
        $this->assertEquals(10, $parser->offsetGet('blockSize'));
        $this->assertEquals('2', $parser->offsetGet('versionNumber'));
    }

}
