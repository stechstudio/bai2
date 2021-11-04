<?php

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
    public function testGroupStatusValid(
        string $line,
        string $expected
    ): void {
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

    // TODO(zmd): public function testAsOfDateValid(): void {}
    // TODO(zmd): public function testAsOfDateMissing(): void {}
    // TODO(zmd): public function testAsOfDateInvalid(): void {}

    // TODO(zmd): public function testAsOfTimeValid(): void {}
    // TODO(zmd): public function testAsOfTimeOptional(): void {}
    // TODO(zmd): public function testAsOfTimeInvalid(): void {}

    // TODO(zmd): public function testCurrencyCodeValid(): void {}
    // TODO(zmd): public function testCurrencyCodeOptional(): void {}
    // TODO(zmd): public function testCurrencyCodeInvalid(): void {}

    // TODO(zmd): public function testAsOfDateModifierValid(): void {}
    // TODO(zmd): public function testAsOfDateModifierOptional(): void {}
    // TODO(zmd): public function testAsOfDateModifierInvalid(): void {}

}
