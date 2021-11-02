<?php

namespace STS\Bai2\Parsers;

use STS\Bai2\Tests\Parsers\RecordParserTestCase;

use STS\Bai2\Exceptions\InvalidTypeException;
use STS\Bai2\Exceptions\MalformedInputException;
use STS\Bai2\Exceptions\InvalidUseException;

/**
 * @group RecordParserTests
 */
final class FileHeaderParserTest extends RecordParserTestCase
{

    protected static string $parserClass = FileHeaderParser::class;

    protected static string $readableParserName = 'File Header';

    protected static string $recordCode = '01';

    protected static string $fullRecordLine = '01,SENDR1,RECVR1,210616,1700,01,80,10,2/';

    protected static string $partialRecordLine = '01,SENDR1,RECVR1/';

    protected static string $continuedRecordLine = '88,210616,1700,01,80,10,2/';

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

    /**
     * Since, if the physical record length is used at all, it is set as a
     * field in the incoming file header line, we cannot know or set that
     * physical line length (and thus validate input line lengths) until we
     * attempt to parse the file header record. That is different from the
     * other record types, where they can be checked immediately based on the
     * physical record length previously specified (and thus known) in the file
     * header prior to they themselves being parsed.
     */
    public function testSettingPhysicalRecordLengthAtConstructToAnythingOtherThanNullThrows(): void
    {
        $this->expectException(InvalidUseException::class);
        $this->expectExceptionMessage(
            'It is an error to try to set the Physical Record Length on a File '
                . "Header before it has been parsed and read the File Header's "
                . 'content.'
        );
        $parser = new FileHeaderParser(physicalRecordLength: 80);
    }

    /**
     * The other record types will know up front their physical line length, if
     * not defaulted, and thus their record length validation will be upon
     * pushing a line (rather than during parse/read). But a File Header only
     * can find out it's physical record length at parse time; thus here the
     * exception gets thrown at field access (which triggers the parse) rather
     * than line push time.
     */
    public function testPhysicalRecordLengthEnforcedOnFirstLine(): void
    {
        $this->parser->pushLine('01,SENDR1,RECVR1,210616,1700,01,30,10,2/');

        $this->expectException(MalformedInputException::class);
        $this->expectExceptionMessage('Input line length exceeds requested physical record length.');
        $this->parser['recordCode'];
    }

    /**
     * Because record parsers are a parse-once deal, we exclude the possibility
     * of setting the physical record length in the first line, parsing it out
     * and using it to set the physical line length within the object, then
     * proceeding to push in further lines (since pushing lines after parse is
     * disallowed).
     */
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
    public function testSenderIdentificationInvalid(string $line): void
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
    public function testReceiverIdentificationInvalid(string $line): void
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
    public function testFileCreationDateInvalid(string $line): void
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
    public function testFileCreationTimeInvalid(string $line): void
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
    public function testFileIdentificationNumberInvalid(string $line): void
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
    public function testPhysicalRecordLengthInvalid(string $line): void
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
    public function testBlockSizeInvalid(string $line): void
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

    public function testVersionNumberInvalid(): void
    {
        $this->parser->pushLine('01,SENDR1,RECVR1,210616,1700,01,80,10,F/');

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Version Number" must be "2" (this library only supports v2 of the BAI format).');
        $this->parser['versionNumber'];
    }

}
