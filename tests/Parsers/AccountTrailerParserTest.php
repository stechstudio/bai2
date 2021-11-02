<?php

namespace STS\Bai2\Parsers;

use STS\Bai2\Tests\Parsers\RecordParserTestCase;

/**
 * @group RecordParserTests
 */
final class AccountTrailerParserTest extends RecordParserTestCase
{

    protected static string $parserClass = AccountTrailerParser::class;

    protected static string $readableParserName = 'Account Trailer';

    protected static string $recordCode = '49';

    // NOTE: this example record comes straight for spec, pg. 22
    protected static string $fullRecordLine = '49,18650000,3/';

    protected static string $partialRecordLine = '49,18650000/';

    protected static string $continuedRecordLine = '88,3/';

    // ----- record-specific parsing and usage ---------------------------------

    public function testParseFromSingleLine(): void
    {
        $this->parser->pushLine(self::$fullRecordLine);

        $this->assertEquals('49', $this->parser['recordCode']);
        $this->assertEquals(18650000, $this->parser['accountControlTotal']);
        $this->assertEquals(3, $this->parser['numberOfRecords']);
    }

    public function testParseFromMultipleLines(): void
    {
        $this->parser->pushLine(self::$partialRecordLine);
        $this->parser->pushLine(self::$continuedRecordLine);

        $this->assertEquals('49', $this->parser['recordCode']);
        $this->assertEquals(18650000, $this->parser['accountControlTotal']);
        $this->assertEquals(3, $this->parser['numberOfRecords']);
    }

    public function testToArray(): void
    {
        $this->parser->pushLine(self::$fullRecordLine);

        $this->assertEquals(
            [
                'recordCode' => '49',
                'accountControlTotal' => 18650000,
                'numberOfRecords' => 3,
            ],
            $this->parser->toArray()
        );
    }

    // ----- record-specific field validation ----------------------------------

    // TODO(zmd): public function testAccountControlCodeValid(): void {}
    // TODO(zmd): public function testAccountControlCodeMissing(): void {}
    // TODO(zmd): public function testAccountControlCodeInvalid(): void {}

    // TODO(zmd): public function testNumberOfRecordsValid(): void {}
    // TODO(zmd): public function testNumberOfRecordsMissing(): void {}
    // TODO(zmd): public function testNumberOfRecordsInvalid(): void {}

}
