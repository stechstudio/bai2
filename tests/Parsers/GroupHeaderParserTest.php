<?php

declare(strict_types=1);

namespace STS\Bai2\Parsers;

use STS\Bai2\Tests\Parsers\RecordParserTestCase;

use STS\Bai2\Exceptions\InvalidTypeException;

/**
 * @group RecordParserTests
 */
final class GroupHeaderParserTest extends RecordParserTestCase
{

    protected static string $parserClass = GroupHeaderParser::class;

    protected static string $readableParserName = 'Group Header';

    protected static string $recordCode = '02';

    protected static string $fullRecordLine = '02,ABC123,XYZ789,1,211027,0800,USD,2/';

    protected static string $partialRecordLine = '02,ABC123,XYZ789/';

    protected static string $continuedRecordLine = '88,1,211027,0800,USD,2/';

    // ----- record-specific parsing and usage ---------------------------------

    public function testParseFromSingleLine(): void
    {
        $this->parser->pushLine(self::$fullRecordLine);

        $this->assertEquals('02', $this->parser['recordCode']);
        $this->assertEquals('ABC123', $this->parser['ultimateReceiverIdentification']);
        $this->assertEquals('XYZ789', $this->parser['originatorIdentification']);
        $this->assertEquals('1', $this->parser['groupStatus']);
        $this->assertEquals('211027', $this->parser['asOfDate']);
        $this->assertEquals('0800', $this->parser['asOfTime']);
        $this->assertEquals('USD', $this->parser['currencyCode']);
        $this->assertEquals('2', $this->parser['asOfDateModifier']);
    }

    public function testParseFromMultipleLines(): void
    {
        $this->parser->pushLine(self::$partialRecordLine);
        $this->parser->pushLine(self::$continuedRecordLine);

        $this->assertEquals('02', $this->parser['recordCode']);
        $this->assertEquals('ABC123', $this->parser['ultimateReceiverIdentification']);
        $this->assertEquals('XYZ789', $this->parser['originatorIdentification']);
        $this->assertEquals('1', $this->parser['groupStatus']);
        $this->assertEquals('211027', $this->parser['asOfDate']);
        $this->assertEquals('0800', $this->parser['asOfTime']);
        $this->assertEquals('USD', $this->parser['currencyCode']);
        $this->assertEquals('2', $this->parser['asOfDateModifier']);
    }

    public function testToArray(): void
    {
        $this->parser->pushLine(self::$fullRecordLine);

        $this->assertEquals(
            [
                'recordCode' => '02',
                'ultimateReceiverIdentification' => 'ABC123',
                'originatorIdentification' => 'XYZ789',
                'groupStatus' => '1',
                'asOfDate' => '211027',
                'asOfTime' => '0800',
                'currencyCode' => 'USD',
                'asOfDateModifier' => '2',
            ],
            $this->parser->toArray()
        );
    }

    // ----- record-specific field validation ----------------------------------

    /**
     * @testWith ["02,ABC123,XYZ789,1,211027,0800,USD,2/", "ABC123"]
     *           ["02,123ABC,XYZ789,1,211027,0800,USD,2/", "123ABC"]
     *           ["02,abc123,XYZ789,1,211027,0800,USD,2/", "abc123"]
     *           ["02,123abc,XYZ789,1,211027,0800,USD,2/", "123abc"]
     *           ["02,abcdef,XYZ789,1,211027,0800,USD,2/", "abcdef"]
     *           ["02,123456,XYZ789,1,211027,0800,USD,2/", "123456"]
     */
    public function testUltimateReceiverIdentificationValid(
        string $line,
        string $expected
    ): void {
        $this->parser->pushLine($line);
        $this->assertEquals($expected, $this->parser['ultimateReceiverIdentification']);
    }

    public function testUltimateReceiverIdentificationOptional(): void
    {
        $this->parser->pushLine('02,,XYZ789,1,211027,0800,USD,2/');
        $this->assertNull($this->parser['ultimateReceiverIdentification']);
    }

    /**
     * @testWith ["02,ABC 123,XYZ789,1,211027,0800,USD,2/"]
     *           ["02, ABC123,XYZ789,1,211027,0800,USD,2/"]
     *           ["02,ABC123 ,XYZ789,1,211027,0800,USD,2/"]
     *           ["02,ABC_123,XYZ789,1,211027,0800,USD,2/"]
     *           ["02,ABC-123,XYZ789,1,211027,0800,USD,2/"]
     *           ["02,ABC+123,XYZ789,1,211027,0800,USD,2/"]
     *           ["02,!@#$%^,XYZ789,1,211027,0800,USD,2/"]
     *           ["02, ,XYZ789,1,211027,0800,USD,2/"]
     */
    public function testUltimateReceiverIdentificationInvalid(string $line): void
    {
        $this->parser->pushLine($line);

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Ultimate Receiver Identification" must be alpha-numeric when provided.');
        $this->parser['ultimateReceiverIdentification'];
    }

    /**
     * @testWith ["02,ABC123,XYZ789,1,211027,0800,USD,2/", "XYZ789"]
     *           ["02,ABC123,789XYZ,1,211027,0800,USD,2/", "789XYZ"]
     *           ["02,ABC123,xyz789,1,211027,0800,USD,2/", "xyz789"]
     *           ["02,ABC123,789xyz,1,211027,0800,USD,2/", "789xyz"]
     *           ["02,ABC123,stwxyz,1,211027,0800,USD,2/", "stwxyz"]
     *           ["02,ABC123,456789,1,211027,0800,USD,2/", "456789"]
     */
    public function testOriginatorIdentificationValid(
        string $line,
        string $expected
    ): void {
        $this->parser->pushLine($line);
        $this->assertEquals($expected, $this->parser['originatorIdentification']);
    }

    public function testOriginatorIdentificationMissing(): void
    {
        $this->parser->pushLine('02,ABC123,,1,211027,0800,USD,2/');

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Originator Identification" cannot be omitted.');
        $this->parser['originatorIdentification'];
    }

    /**
     * @testWith ["02,ABC123,XYZ 789,1,211027,0800,USD,2/"]
     *           ["02,ABC123, XYZ789,1,211027,0800,USD,2/"]
     *           ["02,ABC123,XYZ789 ,1,211027,0800,USD,2/"]
     *           ["02,ABC123,XYZ_789,1,211027,0800,USD,2/"]
     *           ["02,ABC123,XYZ-789,1,211027,0800,USD,2/"]
     *           ["02,ABC123,XYZ+789,1,211027,0800,USD,2/"]
     *           ["02,ABC123,)(*&^%,1,211027,0800,USD,2/"]
     *           ["02,ABC123, ,1,211027,0800,USD,2/"]
     */
    public function testOriginatorIdentificationInvalid(string $line): void
    {
        $this->parser->pushLine($line);

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Originator Identification" must be alpha-numeric.');
        $this->parser['originatorIdentification'];
    }

    /**
     * @testWith ["02,ABC123,XYZ789,1,211027,0800,USD,2/", "1"]
     *           ["02,ABC123,XYZ789,2,211027,0800,USD,2/", "2"]
     *           ["02,ABC123,XYZ789,3,211027,0800,USD,2/", "3"]
     *           ["02,ABC123,XYZ789,4,211027,0800,USD,2/", "4"]
     */
    public function testGroupStatusValid(string $line, string $expected): void
    {
        $this->parser->pushLine($line);
        $this->assertEquals($expected, $this->parser['groupStatus']);
    }

    public function testGroupStatusMissing(): void
    {
        $this->parser->pushLine('02,ABC123,XYZ789,,211027,0800,USD,2/');

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Group Status" cannot be omitted.');
        $this->parser['groupStatus'];
    }

    /**
     * @testWith ["02,ABC123,XYZ789, 1,211027,0800,USD,2/"]
     *           ["02,ABC123,XYZ789,1 ,211027,0800,USD,2/"]
     *           ["02,ABC123,XYZ789,0,211027,0800,USD,2/"]
     *           ["02,ABC123,XYZ789,11,211027,0800,USD,2/"]
     *           ["02,ABC123,XYZ789,5,211027,0800,USD,2/"]
     *           ["02,ABC123,XYZ789,A,211027,0800,USD,2/"]
     */
    public function testGroupStatusInvalid(string $line): void
    {
        $this->parser->pushLine($line);

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Group Status" must be one of 1, 2, 3, or 4.');
        $this->parser['groupStatus'];
    }

    /**
     * @testWith ["02,ABC123,XYZ789,1,211027,0800,USD,2/", "211027"]
     *           ["02,ABC123,XYZ789,1,000000,0800,USD,2/", "000000"]
     *           ["02,ABC123,XYZ789,1,999999,0800,USD,2/", "999999"]
     */
    public function testAsOfDateValid(string $line, string $expected): void
    {
        $this->parser->pushLine($line);
        $this->assertEquals($expected, $this->parser['asOfDate']);
    }

    public function testAsOfDateMissing(): void
    {
        $this->parser->pushLine('02,ABC123,XYZ789,1,,0800,USD,2/');

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "As-of-Date" cannot be omitted.');
        $this->parser['asOfDate'];
    }

    /**
     * @testWith ["02,ABC123,XYZ789,1,a211027,0800,USD,2/"]
     *           ["02,ABC123,XYZ789,1,211027b,0800,USD,2/"]
     *           ["02,ABC123,XYZ789,1,20211027,0800,USD,2/"]
     *           ["02,ABC123,XYZ789,1,21-10-27,0800,USD,2/"]
     *           ["02,ABC123,XYZ789,1,27-Oct 21,0800,USD,2/"]
     */
    public function testAsOfDateInvalid(string $line): void
    {
        $this->parser->pushLine($line);

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "As-of-Date" must be exactly 6 numerals (YYMMDD).');
        $this->parser['asOfDate'];
    }

    /**
     * @testWith ["02,ABC123,XYZ789,1,211027,0800,USD,2/", "0800"]
     *           ["02,ABC123,XYZ789,1,211027,0000,USD,2/", "0000"]
     *           ["02,ABC123,XYZ789,1,211027,2400,USD,2/", "2400"]
     *           ["02,ABC123,XYZ789,1,211027,9999,USD,2/", "9999"]
     */
    public function testAsOfTimeValid(string $line, string $expected): void
    {
        $this->parser->pushLine($line);
        $this->assertEquals($expected, $this->parser['asOfTime']);
    }

    public function testAsOfTimeOptional(): void
    {
        $this->parser->pushLine('02,ABC123,XYZ789,1,211027,,USD,2/');
        $this->assertNull($this->parser['asOfTime']);
    }

    /**
     * @testWith ["02,ABC123,XYZ789,1,211027, 0800,USD,2/"]
     *           ["02,ABC123,XYZ789,1,211027,0800 ,USD,2/"]
     *           ["02,ABC123,XYZ789,1,211027,08:00,USD,2/"]
     *           ["02,ABC123,XYZ789,1,211027,800,USD,2/"]
     */
    public function testAsOfTimeInvalid(string $line): void
    {
        $this->parser->pushLine($line);

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "As-of-Time" must be exactly 4 numerals (HHMM) when provided.');
        $this->parser['asOfTime'];
    }

    /**
     * @testWith ["02,ABC123,XYZ789,1,211027,0800,USD,2/", "USD"]
     *           ["02,ABC123,XYZ789,1,211027,0000,ABC,2/", "ABC"]
     *           ["02,ABC123,XYZ789,1,211027,2400,XYZ,2/", "XYZ"]
     */
    public function testCurrencyCodeValid(string $line, string $expected): void
    {
        $this->parser->pushLine($line);
        $this->assertEquals($expected, $this->parser['currencyCode']);
    }

    public function testCurrencyCodeOptional(): void
    {
        $this->parser->pushLine('02,ABC123,XYZ789,1,211027,0800,,2/');
        $this->assertNull($this->parser['currencyCode']);
    }

    /**
     * @testWith ["02,ABC123,XYZ789,1,211027,0800, USD,2/"]
     *           ["02,ABC123,XYZ789,1,211027,0800,USD ,2/"]
     *           ["02,ABC123,XYZ789,1,211027,0800,AUSD,2/"]
     *           ["02,ABC123,XYZ789,1,211027,0800,UD,2/"]
     *           ["02,ABC123,XYZ789,1,211027,0800,123,2/"]
     *           ["02,ABC123,XYZ789,1,211027,0800,$$$,2/"]
     */
    public function testCurrencyCodeInvalid(string $line): void
    {
        $this->parser->pushLine($line);

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Currency Code" must be exactly 3 uppercase letters when provided.');
        $this->parser['currencyCode'];
    }

    /**
     * @testWith ["02,ABC123,XYZ789,1,211027,0800,USD,1/", "1"]
     *           ["02,ABC123,XYZ789,1,211027,0800,USD,2/", "2"]
     *           ["02,ABC123,XYZ789,1,211027,0800,USD,3/", "3"]
     *           ["02,ABC123,XYZ789,1,211027,0800,USD,4/", "4"]
     */
    public function testAsOfDateModifierValid(
        string $line,
        string $expected
    ): void {
        $this->parser->pushLine($line);
        $this->assertEquals($expected, $this->parser['asOfDateModifier']);
    }

    public function testAsOfDateModifierOptional(): void
    {
        $this->parser->pushLine('02,ABC123,XYZ789,1,211027,0800,USD,/');
        $this->assertNull($this->parser['asOfDateModifier']);
    }

    /**
     * @testWith ["02,ABC123,XYZ789,1,211027,0800,USD, 1/"]
     *           ["02,ABC123,XYZ789,1,211027,0800,USD,1 /"]
     *           ["02,ABC123,XYZ789,1,211027,0800,USD,0/"]
     *           ["02,ABC123,XYZ789,1,211027,0800,USD,11/"]
     *           ["02,ABC123,XYZ789,1,211027,0800,USD,5/"]
     *           ["02,ABC123,XYZ789,1,211027,0800,USD,A/"]
     */
    public function testAsOfDateModifierInvalid(string $line): void
    {
        $this->parser->pushLine($line);

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "As-of-Date Modifier" must be one of 1, 2, 3, or 4 when provided.');
        $this->parser['asOfDateModifier'];
    }

}
