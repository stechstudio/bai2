<?php

namespace STS\Bai2\Parsers;

use STS\Bai2\Tests\Parsers\RecordParserTestCase;

use STS\Bai2\Exceptions\InvalidTypeException;

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

    public function testToArray(): void
    {
        $this->parser->pushLine(self::$fullRecordLine);
        $this->assertEquals(
            [
                'recordCode' => '16',
                'typeCode' => '003',
                'amount' => 10000,
                'fundsType' => [
                    'distributionOfAvailability' => 'D',
                    'availability' => [
                         1 =>  1000,
                         5 => 10000,
                        30 => 25000,
                    ]
                ],
                'bankReferenceNumber' => '123456789',
                'customerReferenceNumber' => '987654321',
                'text' => "The following character is, of all the path separation characters I've ever used, my absolute favorite: /"
            ],
            $this->parser->toArray()
        );
    }

    public function fundsTypeVariationsProducer(): array
    {
        return [
            [
                '16,003,10000,,123456789,987654321,SOME TEXT FTW/',
                [
                    'distributionOfAvailability' => null,
                ]
            ],
            [
                '16,003,10000,0,123456789,987654321,SOME TEXT FTW/',
                [
                    'distributionOfAvailability' => '0',
                ]
            ],
            [
                '16,003,10000,1,123456789,987654321,SOME TEXT FTW/',
                [
                    'distributionOfAvailability' => '1',
                ]
            ],
            [
                '16,003,10000,2,123456789,987654321,SOME TEXT FTW/',
                [
                    'distributionOfAvailability' => '2',
                ]
            ],
            [
                '16,003,10000,V,210909,0800,123456789,987654321,SOME TEXT FTW/',
                [
                    'distributionOfAvailability' => 'V',
                    'valueDate' => '210909',
                    'valueTime' => '0800',
                ]
            ],
            [
                '16,003,10000,V,210909,0000,123456789,987654321,SOME TEXT FTW/',
                [
                    'distributionOfAvailability' => 'V',
                    'valueDate' => '210909',
                    'valueTime' => '0000',
                ]
            ],
            [
                '16,003,10000,V,210909,2400,123456789,987654321,SOME TEXT FTW/',
                [
                    'distributionOfAvailability' => 'V',
                    'valueDate' => '210909',
                    'valueTime' => '2400',
                ]
            ],
            [
                '16,003,10000,V,210909,9999,123456789,987654321,SOME TEXT FTW/',
                [
                    'distributionOfAvailability' => 'V',
                    'valueDate' => '210909',
                    'valueTime' => '9999',
                ]
            ],
            [
                '16,003,10000,V,210909,,123456789,987654321,SOME TEXT FTW/',
                [
                    'distributionOfAvailability' => 'V',
                    'valueDate' => '210909',
                    'valueTime' => null,
                ]
            ],
            [
                '16,003,10000,S,150000,100000,90000,123456789,987654321,SOME TEXT FTW/',
                [
                    'distributionOfAvailability' => 'S',
                    'availability' => [
                        0 => 150000,
                        1 => 100000,
                        2 =>  90000,
                    ]
                ]
            ],
            [
                '16,003,10000,S,-150000,+100000,90000,123456789,987654321,SOME TEXT FTW/',
                [
                    'distributionOfAvailability' => 'S',
                    'availability' => [
                        0 => -150000,
                        1 =>  100000,
                        2 =>   90000,
                    ]
                ]
            ],
            [
                '16,003,10000,D,3,0,150000,1,100000,2,90000,123456789,987654321,SOME TEXT FTW/',
                [
                    'distributionOfAvailability' => 'D',
                    'availability' => [
                        0 => 150000,
                        1 => 100000,
                        2 =>  90000,
                    ]
                ]
            ],
            [
                '16,003,10000,D,5,0,150000,1,100000,3,90000,5,70000,7,50000,123456789,987654321,SOME TEXT FTW/',
                [
                    'distributionOfAvailability' => 'D',
                    'availability' => [
                        0 => 150000,
                        1 => 100000,
                        3 =>  90000,
                        5 =>  70000,
                        7 =>  50000,
                    ]
                ]
            ],
            [
                '16,003,10000,D,30,'
                    . '5,150000,'
                    . '10,145000,'
                    . '15,140000,'
                    . '20,135000,'
                    . '25,130000,'
                    . '30,125000,'
                    . '35,120000,'
                    . '40,125000,'
                    . '45,120000,'
                    . '50,115000,'
                    . '55,110000,'
                    . '60,105000,'
                    . '65,100000,'
                    . '70,95000,'
                    . '75,90000,'
                    . '80,85000,'
                    . '85,80000,'
                    . '90,75000,'
                    . '95,70000,'
                    . '100,65000,'
                    . '105,60000,'
                    . '110,55000,'
                    . '115,50000,'
                    . '120,45000,'
                    . '125,40000,'
                    . '130,35000,'
                    . '135,30000,'
                    . '140,25000,'
                    . '145,20000,'
                    . '150,15000,'
                    . '123456789,987654321,SOME TEXT FTW/',
                [
                    'distributionOfAvailability' => 'D',
                    'availability' => [
                          5 => 150000,
                         10 => 145000,
                         15 => 140000,
                         20 => 135000,
                         25 => 130000,
                         30 => 125000,
                         35 => 120000,
                         40 => 125000,
                         45 => 120000,
                         50 => 115000,
                         55 => 110000,
                         60 => 105000,
                         65 => 100000,
                         70 =>  95000,
                         75 =>  90000,
                         80 =>  85000,
                         85 =>  80000,
                         90 =>  75000,
                         95 =>  70000,
                        100 =>  65000,
                        105 =>  60000,
                        110 =>  55000,
                        115 =>  50000,
                        120 =>  45000,
                        125 =>  40000,
                        130 =>  35000,
                        135 =>  30000,
                        140 =>  25000,
                        145 =>  20000,
                        150 =>  15000,
                    ]
                ]
            ],
        ];
    }

    /**
     * @dataProvider fundsTypeVariationsProducer
     */
    public function testFundsTypeVariations(
        string $input,
        array $expectedFundsType
    ): void {
        $this->parser->pushLine($input);

        $this->assertEquals(
            $expectedFundsType,
            $this->parser['fundsType']
        );
    }

    // ----- record-specific field validation ----------------------------------

    // TODO(zmd): public function testTypeCodeValid(): void {}
    // TODO(zmd): public function testTypeCodeMissing(): void {}
    // TODO(zmd): public function testTypeCodeInvalid(): void {}

    // TODO(zmd): public function testAmountValid(): void {}
    // TODO(zmd): public function testAmountMissing(): void {}
    // TODO(zmd): public function testAmountInvalid(): void {}

    /**
     * @testWith ["16,003,10000,0,123456789,987654321,TEXT OF SUCH IMPORT", "0"]
     *           ["16,003,10000,1,123456789,987654321,TEXT OF SUCH IMPORT", "1"]
     *           ["16,003,10000,2,123456789,987654321,TEXT OF SUCH IMPORT", "2"]
     *           ["16,003,10000,V,210909,0800,123456789,987654321,TEXT OF SUCH IMPORT", "V"]
     *           ["16,003,10000,S,150000,100000,90000,123456789,987654321,TEXT OF SUCH IMPORT", "S"]
     *           ["16,003,10000,D,3,0,150000,1,100000,2,90000,123456789,987654321,TEXT OF SUCH IMPORT", "D"]
     */
    public function testFundsTypeDistributionOfAvailabilityValid(
        string $line,
        string $expected
    ): void {
        $this->parser->pushLine($line);
        $this->assertEquals(
            $expected,
            $this->parser['fundsType']['distributionOfAvailability']
        );
    }

    public function testFundsTypeDistributionOfAvailabilityOptional(): void
    {
        $this->parser->pushLine('16,003,10000,,123456789,987654321,TEXT OF SUCH IMPORT');
        $this->assertNull(
            $this->parser['fundsType']['distributionOfAvailability']
        );
    }

    /**
     * @testWith ["16,003,10000,-1,123456789,987654321,TEXT OF SUCH IMPORT"]
     *           ["16,003,10000,3,123456789,987654321,TEXT OF SUCH IMPORT"]
     *           ["16,003,10000,X,123456789,987654321,TEXT OF SUCH IMPORT"]
     *           ["16,003,10000,_,123456789,987654321,TEXT OF SUCH IMPORT"]
     *           ["16,003,10000,one,123456789,987654321,TEXT OF SUCH IMPORT"]
     *           ["16,003,10000,00,123456789,987654321,TEXT OF SUCH IMPORT"]
     *           ["16,003,10000,01,123456789,987654321,TEXT OF SUCH IMPORT"]
     *           ["16,003,10000,02,123456789,987654321,TEXT OF SUCH IMPORT"]
     *           ["16,003,10000,v,210909,0800,123456789,987654321,TEXT OF SUCH IMPORT"]
     *           ["16,003,10000,s,150000,100000,90000,123456789,987654321,TEXT OF SUCH IMPORT"]
     *           ["16,003,10000,d,3,0,150000,1,100000,2,90000,123456789,987654321,TEXT OF SUCH IMPORT"]
     */
    public function testFundsTypeDistributionOfAvailabilityInvalid(string $line): void
    {
        $this->parser->pushLine($line);

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage(
            'Invalid field type: "Distribution of Availability" for "Funds Type" must be one of "0", "1", "2", "V", "S", "D", or "Z".'
        );
        $this->parser['fundsType'];
    }

    /**
     * @testWith ["16,003,10000,V,210909,0800,123456789,987654321,TEXT OF SUCH IMPORT", "210909"]
     *           ["16,003,10000,V,000000,0800,123456789,987654321,TEXT OF SUCH IMPORT", "000000"]
     *           ["16,003,10000,V,999999,0800,123456789,987654321,TEXT OF SUCH IMPORT", "999999"]
     */
    public function testFundsTypeValueDateValid(string $line, string $expected): void
    {
        $this->parser->pushLine($line);
        $this->assertEquals($expected, $this->parser['fundsType']['valueDate']);
    }

    public function testFundsTypeValueDateMissing(): void
    {
        $this->parser->pushLine('16,003,10000,V,,0800,123456789,987654321,TEXT OF SUCH IMPORT');

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Value Dated Date" cannot be omitted.');
        $this->parser['fundsType'];
    }

    /**
     * @testWith ["16,003,10000,V,a10909,0800,123456789,987654321,TEXT OF SUCH IMPORT"]
     *           ["16,003,10000,V,21090b,0800,123456789,987654321,TEXT OF SUCH IMPORT"]
     *           ["16,003,10000,V,20210909,0800,123456789,987654321,TEXT OF SUCH IMPORT"]
     *           ["16,003,10000,V,21-09-09,0800,123456789,987654321,TEXT OF SUCH IMPORT"]
     *           ["16,003,10000,V,9-Sep 2021,0800,123456789,987654321,TEXT OF SUCH IMPORT"]
     */
    public function testFundsTypeValueDateInvalid(string $line): void
    {
        $this->parser->pushLine($line);

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Value Dated Date" must be exactly 6 numerals (YYMMDD).');
        $this->parser['fundsType'];
    }

    // TODO(zmd): public function testFundsTypeValueTimeValid(): void {}
    // TODO(zmd): public function testFundsTypeValueTimeOptional(): void {}
    // TODO(zmd): public function testFundsTypeValueTimeInvalid(): void {}

    // TODO(zmd): public function testFundsTypeSAvailabilityImmediateValid(): void {}
    // TODO(zmd): public function testFundsTypeSAvailabilityImmediateMissing(): void {}
    // TODO(zmd): public function testFundsTypeSImmediateAvailabilityInvalid(): void {}

    // TODO(zmd): public function testFundsTypeSOneDayAvailabilityValid(): void {}
    // TODO(zmd): public function testFundsTypeSOneDayAvailabilityMissing(): void {}
    // TODO(zmd): public function testFundsTypeSOneDayAvailabilityInvalid(): void {}

    // TODO(zmd): public function testFundsTypeSTwoOrMoreDayAvailabilityValid(): void {}
    // TODO(zmd): public function testFundsTypeSTwoOrMoreDayAvailabilityMissing(): void {}
    // TODO(zmd): public function testFundsTypeSTwoOrMoreDayAvailabilityInvalid(): void {}

    // TODO(zmd): public function testFundsTypeDLengthValid(): void {}
    // TODO(zmd): public function testFundsTypeDLengthMissing(): void {}
    // TODO(zmd): public function testFundsTypeDLengthInvalid(): void {}

    // TODO(zmd): public function testFundsTypeDAvailabilityDayValid(): void {}
    // TODO(zmd): public function testFundsTypeDAvailabilityDayMissing(): void {}
    // TODO(zmd): public function testFundsTypeDAvailabilityDayInvalid(): void {}

    // TODO(zmd): public function testFundsTypeDAvailabilityAmountValid(): void {}
    // TODO(zmd): public function testFundsTypeDAvailabilityAmountMissing(): void {}
    // TODO(zmd): public function testFundsTypeDAvailabilityAmountInvalid(): void {}

    // TODO(zmd): public function testBankReferenceNumberValid(): void {}
    // TODO(zmd): public function testBankReferenceNumberMissing(): void {}
    // TODO(zmd): public function testBankReferenceNumberInvalid(): void {}

    // TODO(zmd): public function testCustomerReferenceNumberValid(): void {}
    // TODO(zmd): public function testCustomerReferenceNumberMissing(): void {}
    // TODO(zmd): public function testCustomerReferenceNumberInvalid(): void {}

    // TODO(zmd): public function testTextValid(): void {}
    // TODO(zmd): public function testTextMissing(): void {}
    // TODO(zmd): public function testTextInvalid(): void {}

}
