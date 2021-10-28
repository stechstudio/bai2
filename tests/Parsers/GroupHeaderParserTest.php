<?php

namespace STS\Bai2\Parsers;

use STS\Bai2\Tests\Parsers\RecordParserTestCase;

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

    private static string $continuedRecordLine = '88,1,211027,0800,USD,2/';

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

    public function testPhysicalRecordLengthEnforcedOnFirstLine(): void
    {
        $parser = new GroupHeaderParser(physicalLineLength: 80);

        $this->expectException(MalformedInputException::class);
        $this->expectExceptionMessage('Input line length exceeds requested physical record length.');
        $parser->pushLine($this->partialRecordLine . str_repeat(' ', 80));
    }

    public function testPhysicalRecordLengthEnforcedOnSubsequentLine(): void
    {
        $parser = new GroupHeaderParser(physicalLineLength: 80);
        $parser->pushLine($this->partialRecordLine);

        $this->expectException(MalformedInputException::class);
        $this->expectExceptionMessage('Input line length exceeds requested physical record length.');
        $parser->pushLine($this->continuedRecordLine . str_repeat(' ', 80));
    }

    // TODO(zmd): public function testToArray(): void {}

    // ----- record-specific field validation ----------------------------------

    // TODO(zmd): test "Ultimate Receiver Identification" validation

    // TODO(zmd): test "Originator Identification" validation

    // TODO(zmd): test "Group Status" validation

    // TODO(zmd): test "As-of-Date" validation

    // TODO(zmd): test "As-of-Time" validation

    // TODO(zmd): test "Currency Code" validation

    // TODO(zmd): test "As-of-Date Modifier" validation

}
