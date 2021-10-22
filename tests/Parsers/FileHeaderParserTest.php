<?php

namespace STS\Bai2\Parsers;

use PHPUnit\Framework\TestCase;

use STS\Bai2\Exceptions\InvalidTypeException;

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

    public function testRecordCodeValid(): void
    {
        $parser = new FileHeaderParser();
        $parser->push(self::$headerLine);
        $this->assertEquals('01', $parser->offsetGet('recordCode'));
    }

    public function testRecordCodeMissing(): void
    {
        $parser = new FileHeaderParser();
        $parser->push(',SENDR1,RECVR1,210616,1700,01,80,10,2/');

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Record Code" cannot be omitted.');
        $parser->offsetGet('recordCode');
    }

    public function testRecordCodeInvalidType(): void
    {
        $parser = new FileHeaderParser();
        $parser->push('ZZ,SENDR1,RECVR1,210616,1700,01,80,10,2/');

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Record Code" must be "01".');
        $parser->offsetGet('recordCode');
    }

    /**
     * @testWith ["01,SENDR1,RECVR1,210616,1700,01,80,10,2/", "SENDR1"]
     *           ["01,1SENDR,RECVR1,210616,1700,01,80,10,2/", "1SENDR"]
     *           ["01,1sendr,RECVR1,210616,1700,01,80,10,2/", "1sendr"]
     *           ["01,sendr1,RECVR1,210616,1700,01,80,10,2/", "sendr1"]
     *           ["01,012345,RECVR1,210616,1700,01,80,10,2/", "012345"]
     *           ["01,42thisIsAVeryLongButStillCompletelyValidIdentifier1337,RECVR1,210616,1700,01,80,10,2/", "42thisIsAVeryLongButStillCompletelyValidIdentifier1337"]
     */
    public function testSenderIdentificationValid(string $line, string $expected): void
    {
        $parser = new FileHeaderParser();
        $parser->push($line);

        $this->assertEquals($expected, $parser->offsetGet('senderIdentification'));
    }

    public function testSenderIdentificationMissing(): void
    {
        $parser = new FileHeaderParser();
        $parser->push('01,,RECVR1,210616,1700,01,80,10,2/');

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Sender Identification" cannot be omitted.');
        $parser->offsetGet('senderIdentification');
    }

    /**
     * @testWith ["01,!@#$%,RECVR1,210616,1700,01,80,10,2/"]
     *           ["01,SENDR 1,RECVR1,210616,1700,01,80,10,2/"]
     *           ["01, SENDR1,RECVR1,210616,1700,01,80,10,2/"]
     *           ["01,SENDR1 ,RECVR1,210616,1700,01,80,10,2/"]
     *           ["01, ,RECVR1,210616,1700,01,80,10,2/"]
     *           ["01,SENDR_1,RECVR1,210616,1700,01,80,10,2/"]
     *           ["01,SENDR-1,RECVR1,210616,1700,01,80,10,2/"]
     */
    public function testSenderIdentificationInvalidType(string $line): void
    {
        $parser = new FileHeaderParser();
        $parser->push($line);

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Sender Identification" must be alpha-numeric.');
        $parser->offsetGet('senderIdentification');
    }

    /**
     * @testWith ["01,SENDR1,RECVR1,210616,1700,01,80,10,2/", "RECVR1"]
     *           ["01,SENDR1,1RECVR,210616,1700,01,80,10,2/", "1RECVR"]
     *           ["01,SENDR1,1recvr,210616,1700,01,80,10,2/", "1recvr"]
     *           ["01,SENDR1,recvr1,210616,1700,01,80,10,2/", "recvr1"]
     *           ["01,SENDR1,012345,210616,1700,01,80,10,2/", "012345"]
     *           ["01,SENDR1,42thisIsAVeryLongButStillCompletelyValidIdentifier1337,210616,1700,01,80,10,2/", "42thisIsAVeryLongButStillCompletelyValidIdentifier1337"]
     */
    public function testReceiverIdentificationValid(string $line, string $expected): void
    {
        $parser = new FileHeaderParser();
        $parser->push($line);

        $this->assertEquals($expected, $parser->offsetGet('receiverIdentification'));
    }

    public function testReceiverIdentificationMissing(): void
    {
        $parser = new FileHeaderParser();
        $parser->push('01,SENDR1,,210616,1700,01,80,10,2/');

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Receiver Identification" cannot be omitted.');
        $parser->offsetGet('receiverIdentification');
    }

    /**
     * @testWith ["01,SENDR1,!@#$%,210616,1700,01,80,10,2/"]
     *           ["01,SENDR1,RECVR 1,210616,1700,01,80,10,2/"]
     *           ["01,SENDR1, RECVR1,210616,1700,01,80,10,2/"]
     *           ["01,SENDR1,RECVR1 ,210616,1700,01,80,10,2/"]
     *           ["01,SENDR1, ,210616,1700,01,80,10,2/"]
     *           ["01,SENDR1,RECVR_1,210616,1700,01,80,10,2/"]
     *           ["01,SENDR1,RECVR-1,210616,1700,01,80,10,2/"]
     */
    public function testReceiverIdentificationInvalidType(string $line): void
    {
        $parser = new FileHeaderParser();
        $parser->push($line);

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Receiver Identification" must be alpha-numeric.');
        $parser->offsetGet('receiverIdentification');
    }

    /**
     * @testWith ["01,SENDR1,RECVR1,210616,1700,01,80,10,2/", "210616"]
     *           ["01,SENDR1,RECVR1,210909,1700,01,80,10,2/", "210909"]
     */
    public function testFileCreationDateValid(string $line, string $expected): void
    {
        $parser = new FileHeaderParser();
        $parser->push($line);

        $this->assertEquals($expected, $parser->offsetGet('fileCreationDate'));
    }

    public function testFileCreationDateMissing(): void
    {
        $parser = new FileHeaderParser();
        $parser->push('01,SENDR1,RECVR1,,1700,01,80,10,2/');

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "File Creation Date" cannot be omitted.');
        $parser->offsetGet('fileCreationDate');
    }

    /**
     * @testWith ["01,SENDR1,RECVR1, 210616,1700,01,80,10,2/"]
     *           ["01,SENDR1,RECVR1,210909 ,1700,01,80,10,2/"]
     *           ["01,SENDR1,RECVR1, 210909 ,1700,01,80,10,2/"]
     *           ["01,SENDR1,RECVR1,NODATE,1700,01,80,10,2/"]
     *           ["01,SENDR1,RECVR1,      ,1700,01,80,10,2/"]
     *           ["01,SENDR1,RECVR1,16-June 2021,1700,01,80,10,2/"]
     *           ["01,SENDR1,RECVR1,9-9-2021,1700,01,80,10,2/"]
     *           ["01,SENDR1,RECVR1,20210616,1700,01,80,10,2/"]
     *           ["01,SENDR1,RECVR1,2109,1700,01,80,10,2/"]
     */
    public function testFileCreationDateInvalidType(string $line): void
    {
        $parser = new FileHeaderParser();
        $parser->push($line);

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "File Creation Date" must be composed of exactly 6 numerals.');
        $parser->offsetGet('fileCreationDate');
    }

    /**
     * @testWith ["01,SENDR1,RECVR1,210616,1700,01,80,10,2/", "1700"]
     *           ["01,SENDR1,RECVR1,210616,0000,01,80,10,2/", "0000"]
     *           ["01,SENDR1,RECVR1,210616,2400,01,80,10,2/", "2400"]
     *           ["01,SENDR1,RECVR1,210616,9999,01,80,10,2/", "9999"]
     */
    public function testFileCreationTimeValid(string $line, string $expected): void
    {
        $parser = new FileHeaderParser();
        $parser->push($line);

        $this->assertEquals($expected, $parser->offsetGet('fileCreationTime'));
    }

    public function testFileCreationTimeMissing(): void
    {
        $parser = new FileHeaderParser();
        $parser->push('01,SENDR1,RECVR1,210616,,01,80,10,2/');

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "File Creation Time" cannot be omitted.');
        $parser->offsetGet('fileCreationTime');
    }

    /**
     * @testWith ["01,SENDR1,RECVR1,210616,01700,01,80,10,2/"]
     *           ["01,SENDR1,RECVR1,210616,170000,01,80,10,2/"]
     *           ["01,SENDR1,RECVR1,210616,170,01,80,10,2/"]
     *           ["01,SENDR1,RECVR1,210616,late,01,80,10,2/"]
     *           ["01,SENDR1,RECVR1,210616,17:00,01,80,10,2/"]
     */
    public function testFileCreationTimeInvalidType(string $line): void
    {
        $parser = new FileHeaderParser();
        $parser->push($line);

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "File Creation Time" must be composed of exactly 4 numerals.');
        $parser->offsetGet('fileCreationTime');
    }

    /**
     * @testWith ["01,SENDR1,RECVR1,210616,1700,01,80,10,2/", "01"]
     *           ["01,SENDR1,RECVR1,210616,0000,10,80,10,2/", "10"]
     *           ["01,SENDR1,RECVR1,210616,2400,00,80,10,2/", "00"]
     *           ["01,SENDR1,RECVR1,210616,2400,9,80,10,2/", "9"]
     *           ["01,SENDR1,RECVR1,210616,2400,00000005,80,10,2/", "00000005"]
     *           ["01,SENDR1,RECVR1,210616,9999,1019020202,80,10,2/", "1019020202"]
     */
    public function testFileIdentificationNumberValid(string $line, string $expected): void
    {
        $parser = new FileHeaderParser();
        $parser->push($line);

        $this->assertEquals($expected, $parser->offsetGet('fileIdentificationNumber'));
    }

    public function testFileIdentificationNumberMissing(): void
    {
        $parser = new FileHeaderParser();
        $parser->push('01,SENDR1,RECVR1,210616,1700,,80,10,2/');

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "File Identification Number" cannot be omitted.');
        $parser->offsetGet('fileIdentificationNumber');
    }

    /**
     * @testWith ["01,SENDR1,RECVR1,210616,1700,a,80,10,2/"]
     *           ["01,SENDR1,RECVR1,210616,1700,abc123,80,10,2/"]
     *           ["01,SENDR1,RECVR1,210616,1700,123abc,80,10,2/"]
     *           ["01,SENDR1,RECVR1,210616,1700,one,80,10,2/"]
     *           ["01,SENDR1,RECVR1,210616,0000,10-4,80,10,2/"]
     *           ["01,SENDR1,RECVR1,210616,2400,6*7,80,10,2/"]
     */
    public function testFileIdentificationNumberInvalidType(string $line): void
    {
        $parser = new FileHeaderParser();
        $parser->push($line);

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "File Identification Number" must be composed of 1 or more numerals.');
        $parser->offsetGet('fileIdentificationNumber');
    }

    // TODO(zmd): testPhysicalRecordLengthValid(string $line, string $expected): void
    // TODO(zmd): testPhysicalRecordLengthInvalidType(string $line): void

    // TODO(zmd): testBlockSizeValid(string $line, string $expected): void
    // TODO(zmd): testBlockSizeInvalidType(string $line): void

    // TODO(zmd): testVersionNumberValid(string $line, string $expected): void
    // TODO(zmd): testVersionNumberMissing(): void
    // TODO(zmd): testVersionNumberInvalidType(string $line): void

}
