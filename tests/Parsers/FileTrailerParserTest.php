<?php

namespace STS\Bai2\Parsers;

use STS\Bai2\Tests\Parsers\RecordParserTestCase;

/**
 * @group RecordParserTests
 */
final class FileTrailerParserTest extends RecordParserTestCase
{

    protected static string $parserClass = FileTrailerParser::class;

    protected static string $readableParserName = 'File Trailer';

    protected static string $recordCode = '99';

    protected static string $fullRecordLine = '99,123456789,5,42/';

    protected static string $partialRecordLine = '99,123456789/';

    protected static string $continuedRecordLine = '88,5,42/';

    // ----- record-specific parsing and usage ---------------------------------

    public function testParseFromSingleLine(): void
    {
        $this->parser->pushLine(self::$fullRecordLine);

        $this->assertEquals('99', $this->parser['recordCode']);
        $this->assertEquals(123456789, $this->parser['fileControlTotal']);
        $this->assertEquals(5, $this->parser['numberOfGroups']);
        $this->assertEquals(42, $this->parser['numberOfRecords']);
    }

    public function testParseFromMultipleLines(): void
    {
        $this->parser->pushLine(self::$partialRecordLine);
        $this->parser->pushLine(self::$continuedRecordLine);

        $this->assertEquals('99', $this->parser['recordCode']);
        $this->assertEquals(123456789, $this->parser['fileControlTotal']);
        $this->assertEquals(5, $this->parser['numberOfGroups']);
        $this->assertEquals(42, $this->parser['numberOfRecords']);
    }

    public function testToArray(): void
    {
        $this->parser->pushLine(self::$fullRecordLine);

        $this->assertEquals(
            [
                'recordCode' => '99',
                'fileControlTotal' => 123456789,
                'numberOfGroups' => 5,
                'numberOfRecords' => 42,
            ],
            $this->parser->toArray()
        );
    }

    // ----- record-specific field validation ----------------------------------

    // TODO(zmd): public function testGroupControlTotalValid(): void {}
    // TODO(zmd): public function testGroupControlTotalMissing(): void {}
    // TODO(zmd): public function testGroupControlTotalInvalid(): void {}

    // TODO(zmd): public function testNumberOfGroupsValid(): void {}
    // TODO(zmd): public function testNumberOfGroupsMissing(): void {}
    // TODO(zmd): public function testNumberOfGroupsInvalid(): void {}

    // TODO(zmd): public function testNumberOfRecordsdValid(): void {}
    // TODO(zmd): public function testNumberOfRecordsdMissing(): void {}
    // TODO(zmd): public function testNumberOfRecordsdInvalid(): void {}

}
