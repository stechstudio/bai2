<?php

namespace STS\Bai2\Parsers;

use STS\Bai2\Tests\Parsers\RecordParserTestCase;

/**
 * @group RecordParserTests
 */
final class GroupTrailerParserTest extends RecordParserTestCase
{

    protected static string $parserClass = GroupTrailerParser::class;

    protected static string $readableParserName = 'Group Trailer';

    protected static string $recordCode = '98';

    // NOTE: this example record comes straight for spec, pg. 23
    protected static string $fullRecordLine = '98,11800000,2,6/';

    protected static string $partialRecordLine = '98,11800000/';

    protected static string $continuedRecordLine = '88,2,6/';

    // ----- record-specific parsing and usage ---------------------------------

    public function testParseFromSingleLine(): void
    {
        $this->parser->pushLine(self::$fullRecordLine);

        $this->assertEquals('98', $this->parser['recordCode']);
        $this->assertEquals(11800000, $this->parser['groupControlTotal']);
        $this->assertEquals(2, $this->parser['numberOfAccounts']);
        $this->assertEquals(6, $this->parser['numberOfRecords']);
    }

    public function testParseFromMultipleLines(): void
    {
        $this->parser->pushLine(self::$partialRecordLine);
        $this->parser->pushLine(self::$continuedRecordLine);

        $this->assertEquals('98', $this->parser['recordCode']);
        $this->assertEquals(11800000, $this->parser['groupControlTotal']);
        $this->assertEquals(2, $this->parser['numberOfAccounts']);
        $this->assertEquals(6, $this->parser['numberOfRecords']);
    }

    public function testToArray(): void
    {
        $this->parser->pushLine(self::$fullRecordLine);

        $this->assertEquals(
            [
                'recordCode' => '98',
                'groupControlTotal' => 11800000,
                'numberOfAccounts' => 2,
                'numberOfRecords' => 6,
            ],
            $this->parser->toArray()
        );
    }

    // ----- record-specific field validation ----------------------------------

    // TODO(zmd): public function testGroupControlTotalValid(): void {}
    // TODO(zmd): public function testGroupControlTotalMissing(): void {}
    // TODO(zmd): public function testGroupControlTotalInvalid(): void {}

    // TODO(zmd): public function testNumberOfAccountsValid(): void {}
    // TODO(zmd): public function testNumberOfAccountsMissing(): void {}
    // TODO(zmd): public function testNumberOfAccountsInvalid(): void {}

    // TODO(zmd): public function testNumberOfRecordsValid(): void {}
    // TODO(zmd): public function testNumberOfRecordsMissing(): void {}
    // TODO(zmd): public function testNumberOfRecordsInvalid(): void {}

}
