<?php

namespace STS\Bai2\Parsers;

use PHPUnit\Framework\TestCase;

use STS\Bai2\Exceptions\InvalidUseException;
use STS\Bai2\Exceptions\InvalidTypeException;
use STS\Bai2\Exceptions\InvalidFieldNameException;
use STS\Bai2\Exceptions\InvalidRecordException;
use STS\Bai2\Exceptions\MalformedInputException;

final class FileHeaderParserTest extends TestCase
{

    protected static string $fullRecordLine = '01,SENDR1,RECVR1,210616,1700,01,80,10,2/';

    protected static string $partialRecordLine = '01,SENDR1,RECVR1/';

    protected static string $continuedRecordLine = '88,210616,1700,01,80,10,2/';

    protected static string $parserClass = FileHeaderParser::class;

    protected string $readableParserName = 'File Header';

    protected FileHeaderParser $parser;

    public function setUp(): void
    {
        $this->parser = new self::$parserClass();
    }

    // ===== common array access trait functionality ===========================

    public function testAccessFieldViaOffsetGet(): void
    {
        $this->parser->pushLine(self::$fullRecordLine);

        $this->assertEquals('01', $this->parser->offsetGet('recordCode'));
    }

    public function testOffsetGetThrowsOnUnknownField(): void
    {
        $this->parser->pushLine(self::$fullRecordLine);

        $this->expectException(InvalidFieldNameException::class);
        $this->expectExceptionMessage("{$this->readableParserName} does not have a \"fooBar\" field.");
        $this->parser->offsetGet('fooBar');
    }

    public function testOffsetGetThrowsIfNoLinesPushed(): void
    {
        $this->expectException(InvalidUseException::class);
        $this->expectExceptionMessage("Cannot parse {$this->readableParserName} without first pushing line(s).");
        $this->parser->offsetGet('recordCode');
    }

    public function testOffsetExistsThrowsIfNoLinesPushed(): void
    {
        $this->expectException(InvalidUseException::class);
        $this->expectExceptionMessage("Cannot parse {$this->readableParserName} without first pushing line(s).");
        $this->parser->offsetGet('recordCode');
    }

    public function testOffsetExistsForExtantField(): void
    {
        $this->parser->pushLine(self::$fullRecordLine);

        $this->assertTrue($this->parser->offsetExists('recordCode'));
    }

    public function testOffsetExistsForNonExtantField(): void
    {
        $this->parser->pushLine(self::$fullRecordLine);

        $this->assertFalse($this->parser->offsetExists('codedRecord'));
    }

    public function testOffsetSetAlwaysThrows(): void
    {
        $this->parser->pushLine(self::$fullRecordLine);

        $this->expectException(InvalidUseException::class);
        $this->expectExceptionMessage('::offsetSet() is unsupported.');
        $this->parser->offsetSet('codedRecord', '23');
    }

    public function testOffsetUnsetAlwaysThrows(): void
    {
        $this->parser->pushLine(self::$fullRecordLine);

        $this->expectException(InvalidUseException::class);
        $this->expectExceptionMessage('::offsetUnset() is unsupported.');
        $this->parser->offsetUnset('codedRecord');
    }

    public function testAccessFieldAsIfFromArray(): void
    {
        $this->parser->pushLine(self::$fullRecordLine);

        $this->assertEquals('01', $this->parser['recordCode']);
    }

    // ===== common record parser usage and validations ========================

    public function testToArrayThrowsIfNoLinesPushed(): void
    {
        $this->expectException(InvalidUseException::class);
        $this->expectExceptionMessage("Cannot parse {$this->readableParserName} without first pushing line(s).");
        $this->parser->toArray();
    }

    /**
     * @testWith ["18,nope,nope,nope/"]
     *           ["This ain't no header line!"]
     */
    public function testPushLineRejectsInvalidHeaderLine(string $invalidHeader): void
    {
        $this->expectException(InvalidRecordException::class);
        $this->expectExceptionMessage("Encountered an invalid or malformed {$this->readableParserName} record.");
        $this->parser->pushLine($invalidHeader);
    }

    /**
     * @testWith ["23,This ain't no continuation line!"]
     *           ["This ain't no continuation line!"]
     */
    public function testPushLineRejectsInvalidContinuationLine(string $invalidContinuation): void
    {
        $this->parser->pushLine(self::$partialRecordLine);

        $this->expectException(InvalidRecordException::class);
        $this->expectExceptionMessage("Encountered an invalid or malformed {$this->readableParserName} continuation.");
        $this->parser->pushLine($invalidContinuation);
    }

    // ----- record-specific parsing and usage ---------------------------------

    public function testParseFromSingleLine(): void
    {
        $this->parser->pushLine(self::$fullRecordLine);

        $this->assertEquals('01', $this->parser['recordCode']);
        $this->assertEquals('SENDR1', $this->parser['senderIdentification']);
        $this->assertEquals('RECVR1', $this->parser['receiverIdentification']);
        $this->assertEquals('210616', $this->parser['fileCreationDate']);
        $this->assertEquals('1700', $this->parser['fileCreationTime']);
        $this->assertEquals('01', $this->parser['fileIdentificationNumber']);
        $this->assertEquals(80, $this->parser['physicalRecordLength']);
        $this->assertEquals(10, $this->parser['blockSize']);
        $this->assertEquals('2', $this->parser['versionNumber']);
    }

    public function testParseFromMultipleLines(): void
    {
        $this->parser->pushLine(self::$partialRecordLine);
        $this->parser->pushLine(self::$continuedRecordLine);

        $this->assertEquals('01', $this->parser['recordCode']);
        $this->assertEquals('SENDR1', $this->parser['senderIdentification']);
        $this->assertEquals('RECVR1', $this->parser['receiverIdentification']);
        $this->assertEquals('210616', $this->parser['fileCreationDate']);
        $this->assertEquals('1700', $this->parser['fileCreationTime']);
        $this->assertEquals('01', $this->parser['fileIdentificationNumber']);
        $this->assertEquals(80, $this->parser['physicalRecordLength']);
        $this->assertEquals(10, $this->parser['blockSize']);
        $this->assertEquals('2', $this->parser['versionNumber']);
    }

    public function testPhysicalRecordLengthEnforcedOnFirstLine(): void
    {
        $this->parser->pushLine('01,SENDR1,RECVR1,210616,1700,01,30,10,2/');

        $this->expectException(MalformedInputException::class);
        $this->expectExceptionMessage('Input line length exceeds requested physical record length.');
        $this->parser['recordCode'];
    }

    public function testPhysicalRecordLengthEnforcedOnSubsequentLine(): void
    {
        $headerLinePartialTooLong = '01,SENDR1,RECVR1/                                                                ';
        $this->parser->pushLine($headerLinePartialTooLong);

        // the continued line contains the physicalRecordLength field, which
        // specifies a length shorter than our initial header line; that's a no
        // go!
        $this->parser->pushLine(self::$continuedRecordLine);

        $this->expectException(MalformedInputException::class);
        $this->expectExceptionMessage('Input line length exceeds requested physical record length.');
        $this->parser['recordCode'];
    }

    public function testToArray(): void
    {
        $this->parser->pushLine(self::$fullRecordLine);

        $this->assertEquals(
            [
                'recordCode' => '01',
                'senderIdentification' => 'SENDR1',
                'receiverIdentification' => 'RECVR1',
                'fileCreationDate' => '210616',
                'fileCreationTime' => '1700',
                'fileIdentificationNumber' => '01',
                'physicalRecordLength' => 80,
                'blockSize' => 10,
                'versionNumber' => '2',
            ],
            $this->parser->toArray()
        );
    }

    // ----- record-specific field validation ----------------------------------

    /**
     * @testWith ["01,SENDR1,RECVR1,210616,1700,01,80,10,2/", "SENDR1"]
     *           ["01,1SENDR,RECVR1,210616,1700,01,80,10,2/", "1SENDR"]
     *           ["01,1sendr,RECVR1,210616,1700,01,80,10,2/", "1sendr"]
     *           ["01,sendr1,RECVR1,210616,1700,01,80,10,2/", "sendr1"]
     *           ["01,012345,RECVR1,210616,1700,01,80,10,2/", "012345"]
     *           ["01,42thisIsAVeryLongButStillCompletelyValidIdentifier1337,RECVR1,210616,1700,01,,10,2/", "42thisIsAVeryLongButStillCompletelyValidIdentifier1337"]
     */
    public function testSenderIdentificationValid(string $line, string $expected): void
    {
        $this->parser->pushLine($line);

        $this->assertEquals($expected, $this->parser['senderIdentification']);
    }

    public function testSenderIdentificationMissing(): void
    {
        $this->parser->pushLine('01,,RECVR1,210616,1700,01,80,10,2/');

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Sender Identification" cannot be omitted.');
        $this->parser['senderIdentification'];
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
        $this->parser->pushLine($line);

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Sender Identification" must be alpha-numeric.');
        $this->parser['senderIdentification'];
    }

    /**
     * @testWith ["01,SENDR1,RECVR1,210616,1700,01,80,10,2/", "RECVR1"]
     *           ["01,SENDR1,1RECVR,210616,1700,01,80,10,2/", "1RECVR"]
     *           ["01,SENDR1,1recvr,210616,1700,01,80,10,2/", "1recvr"]
     *           ["01,SENDR1,recvr1,210616,1700,01,80,10,2/", "recvr1"]
     *           ["01,SENDR1,012345,210616,1700,01,80,10,2/", "012345"]
     *           ["01,SENDR1,42thisIsAVeryLongButStillCompletelyValidIdentifier1337,210616,1700,01,,10,2/", "42thisIsAVeryLongButStillCompletelyValidIdentifier1337"]
     */
    public function testReceiverIdentificationValid(string $line, string $expected): void
    {
        $this->parser->pushLine($line);

        $this->assertEquals($expected, $this->parser['receiverIdentification']);
    }

    public function testReceiverIdentificationMissing(): void
    {
        $this->parser->pushLine('01,SENDR1,,210616,1700,01,80,10,2/');

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Receiver Identification" cannot be omitted.');
        $this->parser['receiverIdentification'];
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
        $this->parser->pushLine($line);

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Receiver Identification" must be alpha-numeric.');
        $this->parser['receiverIdentification'];
    }

    /**
     * @testWith ["01,SENDR1,RECVR1,210616,1700,01,80,10,2/", "210616"]
     *           ["01,SENDR1,RECVR1,210909,1700,01,80,10,2/", "210909"]
     */
    public function testFileCreationDateValid(string $line, string $expected): void
    {
        $this->parser->pushLine($line);

        $this->assertEquals($expected, $this->parser['fileCreationDate']);
    }

    public function testFileCreationDateMissing(): void
    {
        $this->parser->pushLine('01,SENDR1,RECVR1,,1700,01,80,10,2/');

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "File Creation Date" cannot be omitted.');
        $this->parser['fileCreationDate'];
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
        $this->parser->pushLine($line);

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "File Creation Date" must be composed of exactly 6 numerals.');
        $this->parser['fileCreationDate'];
    }

    /**
     * @testWith ["01,SENDR1,RECVR1,210616,1700,01,80,10,2/", "1700"]
     *           ["01,SENDR1,RECVR1,210616,0000,01,80,10,2/", "0000"]
     *           ["01,SENDR1,RECVR1,210616,2400,01,80,10,2/", "2400"]
     *           ["01,SENDR1,RECVR1,210616,9999,01,80,10,2/", "9999"]
     */
    public function testFileCreationTimeValid(string $line, string $expected): void
    {
        $this->parser->pushLine($line);

        $this->assertEquals($expected, $this->parser['fileCreationTime']);
    }

    public function testFileCreationTimeMissing(): void
    {
        $this->parser->pushLine('01,SENDR1,RECVR1,210616,,01,80,10,2/');

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "File Creation Time" cannot be omitted.');
        $this->parser['fileCreationTime'];
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
        $this->parser->pushLine($line);

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "File Creation Time" must be composed of exactly 4 numerals.');
        $this->parser['fileCreationTime'];
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
        $this->parser->pushLine($line);

        $this->assertEquals($expected, $this->parser['fileIdentificationNumber']);
    }

    public function testFileIdentificationNumberMissing(): void
    {
        $this->parser->pushLine('01,SENDR1,RECVR1,210616,1700,,80,10,2/');

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "File Identification Number" cannot be omitted.');
        $this->parser['fileIdentificationNumber'];
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
        $this->parser->pushLine($line);

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "File Identification Number" must be composed of 1 or more numerals.');
        $this->parser['fileIdentificationNumber'];
    }

    /**
     * @testWith ["01,SENDR1,RECVR1,210616,1700,01,80,10,2/", 80]
     *           ["01,SENDR1,RECVR1,210616,0000,01,50,10,2/", 50]
     *           ["01,SENDR1,RECVR1,210616,2400,01,5000,10,2/", 5000]
     *           ["01,SENDR1,RECVR1,210616,2400,01,080,10,2/", 80]
     *           ["01,SENDR1,RECVR1,210616,9999,01,,10,2/", null]
     */
    public function testPhysicalRecordLengthValid(string $line, ?int $expected): void
    {
        $this->parser->pushLine($line);

        $this->assertEquals($expected, $this->parser['physicalRecordLength']);
    }

    /**
     * @testWith ["01,SENDR1,RECVR1,210616,1700,01,eighty,10,2/"]
     *           ["01,SENDR1,RECVR1,210616,1700,01,40*2,10,2/"]
     */
    public function testPhysicalRecordLengthInvalidType(string $line): void
    {
        $this->parser->pushLine($line);

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Physical Record Length", if provided, must be composed of 1 or more numerals.');
        $this->parser['physicalRecordLength'];
    }

    /**
     * @testWith ["01,SENDR1,RECVR1,210616,1700,01,80,10,2/", 10]
     *           ["01,SENDR1,RECVR1,210616,0000,01,80,100,2/", 100]
     *           ["01,SENDR1,RECVR1,210616,2400,01,80,99999,2/", 99999]
     *           ["01,SENDR1,RECVR1,210616,2400,01,80,1,2/", 1]
     *           ["01,SENDR1,RECVR1,210616,2400,01,80,0,2/", 0]
     *           ["01,SENDR1,RECVR1,210616,2400,01,80,010,2/", 10]
     *           ["01,SENDR1,RECVR1,210616,9999,01,80,,2/", null]
     */
    public function testBlockSizeValid(string $line, ?int $expected): void
    {
        $this->parser->pushLine($line);

        $this->assertEquals($expected, $this->parser['blockSize']);
    }

    /**
     * @testWith ["01,SENDR1,RECVR1,210616,1700,01,80,ten,2/"]
     *           ["01,SENDR1,RECVR1,210616,1700,01,80,2*5,2/"]
     */
    public function testBlockSizeInvalidType(string $line): void
    {
        $this->parser->pushLine($line);

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Block Size", if provided, must be composed of 1 or more numerals.');
        $this->parser['blockSize'];
    }

    public function testVersionNumberValid(): void
    {
        $this->parser->pushLine(self::$fullRecordLine);
        $this->assertEquals('2', $this->parser['versionNumber']);
    }

    public function testVersionNumberMissing(): void
    {
        $this->parser->pushLine('01,SENDR1,RECVR1,210616,1700,01,80,10,/');

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Version Number" cannot be omitted.');
        $this->parser['versionNumber'];
    }

    public function testVersionNumberInvalidType(): void
    {
        $this->parser->pushLine('01,SENDR1,RECVR1,210616,1700,01,80,10,F/');

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Version Number" must be "2" (this library only supports v2 of the BAI format).');
        $this->parser['versionNumber'];
    }

}
