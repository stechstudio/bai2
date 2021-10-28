<?php

namespace STS\Bai2\Parsers;

use STS\Bai2\Tests\Parsers\RecordParserTestCase;

/**
 * @group RecordParserTests
 */
final class TransactionParserTest extends RecordParserTestCase
{

    protected static string $parserClass = TransactionParser::class;

    protected static string $readableParserName = 'Transaction';

    protected static string $recordCode = '16';

    protected static string $fullRecordLine = "16,003,10000,D,3,1,1000,5,10000,30,25000,123456789,987654321,The following character is, of all the path separation characters I've ever used, my absolute favorite: /";

    protected static string $partialRecordLine = '16,003,10000,D,3/';

    protected static string $continuedRecordLine = "88,1,1000,5,10000,30,25000,123456789,987654321,The following character is, of all the path separation characters I've ever used, my absolute favorite: /";

    // ----- record-specific parsing and usage ---------------------------------

    // TODO(zmd): public function testParseFromSingleLine(): void {}

    // TODO(zmd): public function testParseFromMultipleLines(): void {}

    // TODO(zmd): public function testToArray(): void {}

    // ----- record-specific field validation ----------------------------------

    // TODO(zmd): test "Type Code" validation

    // TODO(zmd): test "Amount" validation

    // TODO(zmd): test "Funds Type" validation (and composite sub-fields!)

    // TODO(zmd): test "Bank Reference Number" validation

    // TODO(zmd): test "Customer Reference Number" validation

    // TODO(zmd): test "Text" validation

}
