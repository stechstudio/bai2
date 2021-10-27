<?php

namespace STS\Bai2\Parsers;

use STS\Bai2\Tests\Parsers\RecordParserTestCase;

/**
 * @group RecordParserTests
 */
final class AccountHeaderParserTest extends RecordParserTestCase
{

    protected static string $parserClass = AccountHeaderParser::class;

    protected static string $readableParserName = 'Account Identifier and Summary Status';

    protected static string $recordCode = '03';

    // NOTE: this example record comes straight for spec, pg. 17
    protected static string $fullRecordLine = '03,0975312468,,010,500000,,,190,70000000,4,0/';

    protected static string $partialRecordLine = '03,0975312468,,010/';

    private static string $continuedRecordLine = '88,500000,,,190,70000000,4,0/';

    // ----- record-specific parsing and usage ---------------------------------

    // TODO(zmd): public function testParseFromSingleLine(): void {}

    // TODO(zmd): public function testParseFromMultipleLines(): void {}

    // TODO(zmd): public function testPhysicalRecordLengthEnforcedOnFirstLine(): void {}

    // TODO(zmd): public function testPhysicalRecordLengthEnforcedOnSubsequentLine(): void {}

    // TODO(zmd): public function testToArray(): void {}

    // ----- record-specific field validation ----------------------------------

    // TODO(zmd): test "Customer Account Number" validation

    // TODO(zmd): test "Currency Code" validation

    // TODO(zmd): test "Type Code" validation (and composite sub-fields!)

    // TODO(zmd): test     "Amount" validation

    // TODO(zmd): test     "Item Count" validation

    // TODO(zmd): test     "Funds Type" validation (and composite sub-sub-fields!)

}
