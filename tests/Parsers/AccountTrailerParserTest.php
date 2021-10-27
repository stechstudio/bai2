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

    private static string $continuedRecordLine = '88,3/';

    // ----- record-specific parsing and usage ---------------------------------

    // TODO(zmd): public function testParseFromSingleLine(): void {}

    // TODO(zmd): public function testParseFromMultipleLines(): void {}

    // TODO(zmd): public function testPhysicalRecordLengthEnforcedOnFirstLine(): void {}

    // TODO(zmd): public function testPhysicalRecordLengthEnforcedOnSubsequentLine(): void {}

    // TODO(zmd): public function testToArray(): void {}

    // ----- record-specific field validation ----------------------------------

    // TODO(zmd): test "Account Control Code" validation

    // TODO(zmd): test "Number of Records" validation

}
