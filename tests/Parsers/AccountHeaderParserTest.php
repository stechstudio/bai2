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

    protected static string $continuedRecordLine = '88,500000,,,190,70000000,4,0/';

    // ----- record-specific parsing and usage ---------------------------------

    /**
     *  --   ----------   -   :  ---   ------   -   -    :  ---   --------   -   -
     *  03 , 0975312468 ,   ,    010 , 500000 ,   ,   ,     190 , 70000000 , 4 , 0 /
     *                           ^                          ^
     *                           |                          |
     *                   acct status follows        acct summary follows
     *
     *  TODO(zmd): use @testWith with variations, need to have plenty of tests
     *    for the field variations dependent on what type code and funds type
     *    are represented. Important to be mindful of the funds types
     *    variations as well!
     */
    public function testParseFromSingleLine(): void
    {
        $this->parser->pushLine(self::$fullRecordLine);

        $this->assertEquals('03', $this->parser['recordCode']);
        $this->assertEquals('0975312468', $this->parser['customerAccountNumber']);
        $this->assertEquals(null, $this->parser['currencyCode']);

        // TODO(zmd): finish writing me!
    }

    // TODO(zmd): public function testParseFromMultipleLines(): void {}

    // TODO(zmd): public function testToArray(): void {}

    // ----- record-specific field validation ----------------------------------

    // TODO(zmd): test "Customer Account Number" validation

    // TODO(zmd): test "Currency Code" validation

    // TODO(zmd): test "Type Code" validation (and composite sub-fields!)

    // TODO(zmd): test     "Amount" validation

    // TODO(zmd): test     "Item Count" validation

    // TODO(zmd): test     "Funds Type" validation (and composite sub-sub-fields!)

}
