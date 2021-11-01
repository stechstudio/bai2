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

    public function testParseFromSingleLine(): void
    {
        $this->parser->pushLine(self::$fullRecordLine);

        $this->assertEquals('16', $this->parser['recordCode']);
        $this->assertEquals('003', $this->parser['typeCode']);
        $this->assertEquals(10000, $this->parser['amount']);
        $this->assertEquals(
            [
                'distributionOfAvailability' => 'D',
                'availability' => [
                     1 =>  1000,
                     5 => 10000,
                    30 => 25000,
                ]
            ],
            $this->parser['fundsType']
        );
        $this->assertEquals('123456789', $this->parser['bankReferenceNumber']);
        $this->assertEquals('987654321', $this->parser['customerReferenceNumber']);
        $this->assertEquals(
            "The following character is, of all the path separation characters I've ever used, my absolute favorite: /",
            $this->parser['text']
        );
    }

    public function testParseFromMultipleLines(): void
    {
        $this->parser->pushLine(self::$partialRecordLine);
        $this->parser->pushLine(self::$continuedRecordLine);

        $this->assertEquals('16', $this->parser['recordCode']);
        $this->assertEquals('003', $this->parser['typeCode']);
        $this->assertEquals(10000, $this->parser['amount']);
        $this->assertEquals(
            [
                'distributionOfAvailability' => 'D',
                'availability' => [
                     1 =>  1000,
                     5 => 10000,
                    30 => 25000,
                ]
            ],
            $this->parser['fundsType']
        );
        $this->assertEquals('123456789', $this->parser['bankReferenceNumber']);
        $this->assertEquals('987654321', $this->parser['customerReferenceNumber']);
        $this->assertEquals(
            "The following character is, of all the path separation characters I've ever used, my absolute favorite: /",
            $this->parser['text']
        );
    }

    // TODO(zmd): public function testToArray(): void {}

    // ----- record-specific field validation ----------------------------------

    // TODO(zmd): test "Type Code" validation

    // TODO(zmd): test "Amount" validation

    // TODO(zmd): test "Funds Type" validation (and composite sub-fields!)

    // TODO(zmd): test "Bank Reference Number" validation

    // TODO(zmd): test "Customer Reference Number" validation

    // TODO(zmd): test "Text" validation

}
