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

    // TODO(zmd): public function testParseFromSingleLine(): void {}

    // TODO(zmd): public function testParseFromMultipleLines(): void {}

    // TODO(zmd): public function testToArray(): void {}

    // ----- record-specific field validation ----------------------------------

    // TODO(zmd): test "Group Control Total" validation

    // TODO(zmd): test "Number of Accounts" validation

    // TODO(zmd): test "Number of Records" validation

}
