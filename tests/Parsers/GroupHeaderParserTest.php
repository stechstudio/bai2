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

    // TODO(zmd): public function testUltimateReceiverIdentificationValid(): void {}
    // TODO(zmd): public function testUltimateReceiverIdentificationMissing(): void {}
    // TODO(zmd): public function testUltimateReceiverIdentificationInvalid(): void {}

    // TODO(zmd): public function testOriginatorIdentificationValid(): void {}
    // TODO(zmd): public function testOriginatorIdentificationMissing(): void {}
    // TODO(zmd): public function testOriginatorIdentificationInvalid(): void {}

    // TODO(zmd): public function testGroupStatusValid(): void {}
    // TODO(zmd): public function testGroupStatusMissing(): void {}
    // TODO(zmd): public function testGroupStatusInvalid(): void {}

    // TODO(zmd): public function testAsOfDateValid(): void {}
    // TODO(zmd): public function testAsOfDateMissing(): void {}
    // TODO(zmd): public function testAsOfDateInvalid(): void {}

    // TODO(zmd): public function testAsOfTimeValid(): void {}
    // TODO(zmd): public function testAsOfTimeMissing(): void {}
    // TODO(zmd): public function testAsOfTimeInvalid(): void {}

    // TODO(zmd): public function testCurrencyCodeValid(): void {}
    // TODO(zmd): public function testCurrencyCodeMissing(): void {}
    // TODO(zmd): public function testCurrencyCodeInvalid(): void {}

    // TODO(zmd): public function testAsOfDateModifierValid(): void {}
    // TODO(zmd): public function testAsOfDateModifierMissing(): void {}
    // TODO(zmd): public function testAsOfDateModifierInvalid(): void {}

}
