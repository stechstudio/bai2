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

    protected static string $fullRecordLine = "16,409,10000,D,3,1,1000,5,10000,30,25000,123456789,987654321,The following character is, of all the path separation characters I've ever used, my absolute favorite: /";

    protected static string $partialRecordLine = '16,409,10000,D,3/';

    protected static string $continuedRecordLine = "88,1,1000,5,10000,30,25000,123456789,987654321,The following character is, of all the path separation characters I've ever used, my absolute favorite: /";

    // ----- record-specific parsing and usage ---------------------------------

    public function testParseFromSingleLine(): void
    {
        $this->parser->pushLine(self::$fullRecordLine);

        $this->assertEquals('16', $this->parser['recordCode']);
        $this->assertEquals('409', $this->parser['typeCode']);
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
        $this->assertEquals('409', $this->parser['typeCode']);
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
                'typeCode' => '409',
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
                '16,409,10000,,123456789,987654321,SOME TEXT FTW/',
                [
                    'distributionOfAvailability' => null,
                ]
            ],
            [
                '16,409,10000,0,123456789,987654321,SOME TEXT FTW/',
                [
                    'distributionOfAvailability' => '0',
                ]
            ],
            [
                '16,409,10000,1,123456789,987654321,SOME TEXT FTW/',
                [
                    'distributionOfAvailability' => '1',
                ]
            ],
            [
                '16,409,10000,2,123456789,987654321,SOME TEXT FTW/',
                [
                    'distributionOfAvailability' => '2',
                ]
            ],
            [
                '16,409,10000,V,210909,0800,123456789,987654321,SOME TEXT FTW/',
                [
                    'distributionOfAvailability' => 'V',
                    'valueDate' => '210909',
                    'valueTime' => '0800',
                ]
            ],
            [
                '16,409,10000,V,210909,0000,123456789,987654321,SOME TEXT FTW/',
                [
                    'distributionOfAvailability' => 'V',
                    'valueDate' => '210909',
                    'valueTime' => '0000',
                ]
            ],
            [
                '16,409,10000,V,210909,2400,123456789,987654321,SOME TEXT FTW/',
                [
                    'distributionOfAvailability' => 'V',
                    'valueDate' => '210909',
                    'valueTime' => '2400',
                ]
            ],
            [
                '16,409,10000,V,210909,9999,123456789,987654321,SOME TEXT FTW/',
                [
                    'distributionOfAvailability' => 'V',
                    'valueDate' => '210909',
                    'valueTime' => '9999',
                ]
            ],
            [
                '16,409,10000,V,210909,,123456789,987654321,SOME TEXT FTW/',
                [
                    'distributionOfAvailability' => 'V',
                    'valueDate' => '210909',
                    'valueTime' => null,
                ]
            ],
            [
                '16,409,10000,S,150000,100000,90000,123456789,987654321,SOME TEXT FTW/',
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
                '16,409,10000,S,-150000,+100000,90000,123456789,987654321,SOME TEXT FTW/',
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
                '16,409,10000,D,3,0,150000,1,100000,2,90000,123456789,987654321,SOME TEXT FTW/',
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
                '16,409,10000,D,5,0,150000,1,100000,3,90000,5,70000,7,50000,123456789,987654321,SOME TEXT FTW/',
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
                '16,409,10000,D,30,'
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

    /**
     * @testWith ["16,101,10000,0,123456789,987654321,TEXT OF SUCH IMPORT", "101"]
     *           ["16,399,10000,0,123456789,987654321,TEXT OF SUCH IMPORT", "399"]
     *           ["16,401,10000,0,123456789,987654321,TEXT OF SUCH IMPORT", "401"]
     *           ["16,699,10000,0,123456789,987654321,TEXT OF SUCH IMPORT", "699"]
     *           ["16,700,10000,0,123456789,987654321,TEXT OF SUCH IMPORT", "700"]
     *           ["16,799,10000,0,123456789,987654321,TEXT OF SUCH IMPORT", "799"]
     *           ["16,890,,,,,TEXT OF SUCH IMPORT", "890"]
     *           ["16,900,,,,,TEXT OF SUCH IMPORT", "900"]
     *           ["16,999,,,,,TEXT OF SUCH IMPORT", "999"]
     */
    public function testTypeCodeValid(string $line, string $expected): void
    {
        $this->parser->pushLine($line);
        $this->assertEquals($expected, $this->parser['typeCode']);
    }

    public function testTypeCodeMissing(): void
    {
        $this->parser->pushLine('16,,10000,0,123456789,987654321,TEXT OF SUCH IMPORT');

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Type Code" cannot be omitted.');
        $this->parser['typeCode'];
    }

    /**
     * @testWith ["16,ABC,10000,0,123456789,987654321,TEXT OF SUCH IMPORT"]
     *           ["16,99,10000,0,123456789,987654321,TEXT OF SUCH IMPORT"]
     *           ["16,0401,10000,0,123456789,987654321,TEXT OF SUCH IMPORT"]
     *           ["16,4-1,10000,0,123456789,987654321,TEXT OF SUCH IMPORT"]
     */
    public function testTypeCodeInvalid(string $line): void
    {
        $this->parser->pushLine($line);

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Type Code" must be composed of exactly three numerals.');
        $this->parser['typeCode'];
    }

    /**
     * @testWith ["16,001,10000,0,123456789,987654321,TEXT OF SUCH IMPORT"]
     *           ["16,100,10000,0,123456789,987654321,TEXT OF SUCH IMPORT"]
     *           ["16,400,10000,0,123456789,987654321,TEXT OF SUCH IMPORT"]
     *           ["16,800,10000,0,123456789,987654321,TEXT OF SUCH IMPORT"]
     *           ["16,891,10000,0,123456789,987654321,TEXT OF SUCH IMPORT"]
     *           ["16,899,10000,0,123456789,987654321,TEXT OF SUCH IMPORT"]
     */
    public function testTypeCodeOutOfRange(string $line): void
    {
        $this->parser->pushLine($line);

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Type Code" was out outside the valid range for transaction detail data.');
        $this->parser['typeCode'];
    }

    public function testNonMonetaryAmountValid(): void
    {
        $this->parser->pushLine('16,890,,,123456789,987654321,TEXT OF SUCH IMPORT');
        $this->assertNull($this->parser['amount']);
    }

    public function testNonMonetaryAmountInvalid(): void
    {
        $this->parser->pushLine('16,890,10000,,123456789,987654321,TEXT OF SUCH IMPORT');

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Amount" should be defaulted since "Type Code" was non-monetary.');
        $this->parser['amount'];
    }

    public function testNonMonetaryFundsTypeValid(): void
    {
        $this->parser->pushLine('16,890,,,123456789,987654321,TEXT OF SUCH IMPORT');
        $this->assertNull($this->parser['fundsType']['distributionOfAvailability']);
    }

    public function testNonMonetaryFundsTypeInvalid(): void
    {
        $this->parser->pushLine('16,890,,0,123456789,987654321,TEXT OF SUCH IMPORT');

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Funds Type" should be defaulted since "Type Code" was non-monetary.');
        $this->parser['fundsType'];
    }

    /**
     * @testWith ["16,409,0,0,123456789,987654321,TEXT OF SUCH IMPORT", 0]
     *           ["16,409,1,0,123456789,987654321,TEXT OF SUCH IMPORT", 1]
     *           ["16,409,10000,0,123456789,987654321,TEXT OF SUCH IMPORT", 10000]
     *           ["16,409,010000,0,123456789,987654321,TEXT OF SUCH IMPORT", 10000]
     */
    public function testAmountValid(string $line, int $expected): void
    {
        $this->parser->pushLine($line);
        $this->assertEquals($expected, $this->parser['amount']);
    }

    public function testAmountOptional(): void
    {
        $this->parser->pushLine('16,409,,0,123456789,987654321,TEXT OF SUCH IMPORT');
        $this->assertNull($this->parser['amount']);
    }

    /**
     * @testWith ["16,409,a10000,0,123456789,987654321,TEXT OF SUCH IMPORT"]
     *           ["16,409,10000b,0,123456789,987654321,TEXT OF SUCH IMPORT"]
     *           ["16,409,10_000,0,123456789,987654321,TEXT OF SUCH IMPORT"]
     *           ["16,409,10+000,0,123456789,987654321,TEXT OF SUCH IMPORT"]
     *           ["16,409,+10000,0,123456789,987654321,TEXT OF SUCH IMPORT"]
     *           ["16,409,-10000,0,123456789,987654321,TEXT OF SUCH IMPORT"]
     */
    public function testAmountInvalid(string $line): void
    {
        $this->parser->pushLine($line);

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Amount" should be an unsigned integer when provided.');
        $this->parser['amount'];
    }

    /**
     * @testWith ["16,409,10000,0,123456789,987654321,TEXT OF SUCH IMPORT", "0"]
     *           ["16,409,10000,1,123456789,987654321,TEXT OF SUCH IMPORT", "1"]
     *           ["16,409,10000,2,123456789,987654321,TEXT OF SUCH IMPORT", "2"]
     *           ["16,409,10000,V,210909,0800,123456789,987654321,TEXT OF SUCH IMPORT", "V"]
     *           ["16,409,10000,S,150000,100000,90000,123456789,987654321,TEXT OF SUCH IMPORT", "S"]
     *           ["16,409,10000,D,3,0,150000,1,100000,2,90000,123456789,987654321,TEXT OF SUCH IMPORT", "D"]
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
        $this->parser->pushLine('16,409,10000,,123456789,987654321,TEXT OF SUCH IMPORT');
        $this->assertNull($this->parser['fundsType']['distributionOfAvailability']);
    }

    /**
     * @testWith ["16,409,10000,-1,123456789,987654321,TEXT OF SUCH IMPORT"]
     *           ["16,409,10000,3,123456789,987654321,TEXT OF SUCH IMPORT"]
     *           ["16,409,10000,X,123456789,987654321,TEXT OF SUCH IMPORT"]
     *           ["16,409,10000,_,123456789,987654321,TEXT OF SUCH IMPORT"]
     *           ["16,409,10000,one,123456789,987654321,TEXT OF SUCH IMPORT"]
     *           ["16,409,10000,00,123456789,987654321,TEXT OF SUCH IMPORT"]
     *           ["16,409,10000,01,123456789,987654321,TEXT OF SUCH IMPORT"]
     *           ["16,409,10000,02,123456789,987654321,TEXT OF SUCH IMPORT"]
     *           ["16,409,10000,v,210909,0800,123456789,987654321,TEXT OF SUCH IMPORT"]
     *           ["16,409,10000,s,150000,100000,90000,123456789,987654321,TEXT OF SUCH IMPORT"]
     *           ["16,409,10000,d,3,0,150000,1,100000,2,90000,123456789,987654321,TEXT OF SUCH IMPORT"]
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
     * @testWith ["16,409,10000,V,210909,0800,123456789,987654321,TEXT OF SUCH IMPORT", "210909"]
     *           ["16,409,10000,V,000000,0800,123456789,987654321,TEXT OF SUCH IMPORT", "000000"]
     *           ["16,409,10000,V,999999,0800,123456789,987654321,TEXT OF SUCH IMPORT", "999999"]
     */
    public function testFundsTypeValueDateValid(string $line, string $expected): void
    {
        $this->parser->pushLine($line);
        $this->assertEquals($expected, $this->parser['fundsType']['valueDate']);
    }

    public function testFundsTypeValueDateMissing(): void
    {
        $this->parser->pushLine('16,409,10000,V,,0800,123456789,987654321,TEXT OF SUCH IMPORT');

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Value Dated Date" cannot be omitted.');
        $this->parser['fundsType'];
    }

    /**
     * @testWith ["16,409,10000,V,a10909,0800,123456789,987654321,TEXT OF SUCH IMPORT"]
     *           ["16,409,10000,V,21090b,0800,123456789,987654321,TEXT OF SUCH IMPORT"]
     *           ["16,409,10000,V,20210909,0800,123456789,987654321,TEXT OF SUCH IMPORT"]
     *           ["16,409,10000,V,21-09-09,0800,123456789,987654321,TEXT OF SUCH IMPORT"]
     *           ["16,409,10000,V,9-Sep 2021,0800,123456789,987654321,TEXT OF SUCH IMPORT"]
     */
    public function testFundsTypeValueDateInvalid(string $line): void
    {
        $this->parser->pushLine($line);

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Value Dated Date" must be exactly 6 numerals (YYMMDD).');
        $this->parser['fundsType'];
    }

    /**
     * @testWith ["16,409,10000,V,210909,0800,123456789,987654321,TEXT OF SUCH IMPORT", "0800"]
     *           ["16,409,10000,V,210909,0000,123456789,987654321,TEXT OF SUCH IMPORT", "0000"]
     *           ["16,409,10000,V,210909,2400,123456789,987654321,TEXT OF SUCH IMPORT", "2400"]
     *           ["16,409,10000,V,210909,9999,123456789,987654321,TEXT OF SUCH IMPORT", "9999"]
     */
    public function testFundsTypeValueTimeValid(string $line, string $expected): void
    {
        $this->parser->pushLine($line);
        $this->assertEquals($expected, $this->parser['fundsType']['valueTime']);
    }

    public function testFundsTypeValueTimeOptional(): void
    {
        $this->parser->pushLine('16,409,10000,V,210909,,123456789,987654321,TEXT OF SUCH IMPORT');
        $this->assertNull($this->parser['fundsType']['valueTime']);
    }

    /**
     * @testWith ["16,409,10000,V,210909,a800,123456789,987654321,TEXT OF SUCH IMPORT"]
     *           ["16,409,10000,V,210909,080b,123456789,987654321,TEXT OF SUCH IMPORT"]
     *           ["16,409,10000,V,210909,08:00,123456789,987654321,TEXT OF SUCH IMPORT"]
     *           ["16,409,10000,V,210909,800,123456789,987654321,TEXT OF SUCH IMPORT"]
     */
    public function testFundsTypeValueTimeInvalid(string $line): void
    {
        $this->parser->pushLine($line);

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Value Dated Time" must be exactly 4 numerals (HHMM) when provided.');
        $this->parser['fundsType'];
    }

    /**
     * @testWith ["16,409,10000,S,150000,100000,90000,123456789,987654321,TEXT OF SUCH IMPORT", 150000]
     *           ["16,409,10000,S,+150000,100000,90000,123456789,987654321,TEXT OF SUCH IMPORT", 150000]
     *           ["16,409,10000,S,-150000,100000,90000,123456789,987654321,TEXT OF SUCH IMPORT", -150000]
     */
    public function testFundsTypeSImmediateAvailabilityValid(
        string $line,
        int $expected
    ): void {
        $this->parser->pushLine($line);
        $this->assertEquals($expected, $this->parser['fundsType']['availability'][0]);
    }

    public function testFundsTypeSImmediateAvailabilityMissing(): void
    {
        $this->parser->pushLine('16,409,10000,S,,100000,90000,123456789,987654321,TEXT OF SUCH IMPORT');

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Immediate Availability" cannot be omitted.');
        $this->parser['fundsType'];
    }

    /**
     * @testWith ["16,409,10000,S,a150000,100000,90000,123456789,987654321,TEXT OF SUCH IMPORT"]
     *           ["16,409,10000,S,150000b,100000,90000,123456789,987654321,TEXT OF SUCH IMPORT"]
     *           ["16,409,10000,S,150_000,100000,90000,123456789,987654321,TEXT OF SUCH IMPORT"]
     *           ["16,409,10000,S,150000.00,100000,90000,123456789,987654321,TEXT OF SUCH IMPORT"]
     */
    public function testFundsTypeSImmediateAvailabilityInvalid(string $line): void
    {
        $this->parser->pushLine($line);

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Immediate Availability" must be a signed or unsigned integer value.');
        $this->parser['fundsType'];
    }

    /**
     * @testWith ["16,409,10000,S,150000,100000,90000,123456789,987654321,TEXT OF SUCH IMPORT", 100000]
     *           ["16,409,10000,S,150000,+100000,90000,123456789,987654321,TEXT OF SUCH IMPORT", 100000]
     *           ["16,409,10000,S,150000,-100000,90000,123456789,987654321,TEXT OF SUCH IMPORT", -100000]
     */
    public function testFundsTypeSOneDayAvailabilityValid(
        string $line,
        int $expected
    ): void {
        $this->parser->pushLine($line);
        $this->assertEquals($expected, $this->parser['fundsType']['availability'][1]);
    }

    public function testFundsTypeSOneDayAvailabilityMissing(): void
    {
        $this->parser->pushLine('16,409,10000,S,150000,,90000,123456789,987654321,TEXT OF SUCH IMPORT');

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "One-day Availability" cannot be omitted.');
        $this->parser['fundsType'];
    }

    /**
     * @testWith ["16,409,10000,S,150000,a100000,90000,123456789,987654321,TEXT OF SUCH IMPORT"]
     *           ["16,409,10000,S,150000,100000b,90000,123456789,987654321,TEXT OF SUCH IMPORT"]
     *           ["16,409,10000,S,150000,100_000,90000,123456789,987654321,TEXT OF SUCH IMPORT"]
     *           ["16,409,10000,S,150000,100000.00,90000,123456789,987654321,TEXT OF SUCH IMPORT"]
     */
    public function testFundsTypeSOneDayAvailabilityInvalid(string $line): void
    {
        $this->parser->pushLine($line);

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "One-day Availability" must be a signed or unsigned integer value.');
        $this->parser['fundsType'];
    }

    /**
     * @testWith ["16,409,10000,S,150000,100000,90000,123456789,987654321,TEXT OF SUCH IMPORT", 90000]
     *           ["16,409,10000,S,150000,100000,+90000,123456789,987654321,TEXT OF SUCH IMPORT", 90000]
     *           ["16,409,10000,S,150000,100000,-90000,123456789,987654321,TEXT OF SUCH IMPORT", -90000]
     */
    public function testFundsTypeSTwoOrMoreDayAvailabilityValid(
        string $line,
        int $expected
    ): void {
        $this->parser->pushLine($line);
        $this->assertEquals($expected, $this->parser['fundsType']['availability'][2]);
    }

    public function testFundsTypeSTwoOrMoreDayAvailabilityMissing(): void
    {
        $this->parser->pushLine('16,409,10000,S,150000,100000,,123456789,987654321,TEXT OF SUCH IMPORT');

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Two-or-more Day Availability" cannot be omitted.');
        $this->parser['fundsType'];
    }

    /**
     * @testWith ["16,409,10000,S,150000,100000,a90000,123456789,987654321,TEXT OF SUCH IMPORT"]
     *           ["16,409,10000,S,150000,100000,90000b,123456789,987654321,TEXT OF SUCH IMPORT"]
     *           ["16,409,10000,S,150000,100000,90_000,123456789,987654321,TEXT OF SUCH IMPORT"]
     *           ["16,409,10000,S,150000,100000,90000.00,123456789,987654321,TEXT OF SUCH IMPORT"]
     */
    public function testFundsTypeSTwoOrMoreDayAvailabilityInvalid(string $line): void
    {
        $this->parser->pushLine($line);

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Two-or-more Day Availability" must be a signed or unsigned integer value.');
        $this->parser['fundsType'];
    }

    /**
     * @testWith ["16,409,10000,D,0,123456789,987654321,TEXT OF SUCH IMPORT", 0]
     *           ["16,409,10000,D,1,0,70000000,123456789,987654321,TEXT OF SUCH IMPORT", 1]
     *           ["16,409,10000,D,3,0,50000000,1,15000000,2,5000000,123456789,987654321,TEXT OF SUCH IMPORT", 3]
     *           ["16,409,10000,D,5,0,150000,1,100000,3,90000,5,70000,7,50000,123456789,987654321,TEXT OF SUCH IMPORT", 5]
     *           ["16,409,10000,D,30,5,150000,10,145000,15,140000,20,135000,25,130000,30,125000,35,120000,40,125000,45,120000,50,115000,55,110000,60,105000,65,100000,70,95000,75,90000,80,85000,85,80000,90,75000,95,70000,100,65000,105,60000,110,55000,115,50000,120,45000,125,40000,130,35000,135,30000,140,25000,145,20000,150,15000,123456789,987654321,TEXT OF SUCH IMPORT", 30]
     */
    public function testFundsTypeDLengthValid(string $line, int $expected): void
    {
        $this->parser->pushLine($line);
        $this->assertEquals(
            $expected,
            count($this->parser['fundsType']['availability'])
        );
    }

    public function testFundsTypeDLengthMissing(): void
    {
        $this->parser->pushLine('16,409,10000,D,,0,70000000,123456789,987654321,TEXT OF SUCH IMPORT');

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Number of Distributions" cannot be omitted.');
        $this->parser['fundsType'];
    }

    /**
     * @testWith ["16,409,10000,D,a1,0,70000000,123456789,987654321,TEXT OF SUCH IMPORT"]
     *           ["16,409,10000,D,1b,0,70000000,123456789,987654321,TEXT OF SUCH IMPORT"]
     *           ["16,409,10000,D,-1,0,70000000,123456789,987654321,TEXT OF SUCH IMPORT"]
     *           ["16,409,10000,D,+1,0,70000000,123456789,987654321,TEXT OF SUCH IMPORT"]
     *           ["16,409,10000,D,1.0,0,70000000,123456789,987654321,TEXT OF SUCH IMPORT"]
     */
    public function testFundsTypeDLengthInvalid(string $line): void
    {
        $this->parser->pushLine($line);

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Number of Distributions" should be an unsigned integer.');
        $this->parser['fundsType'];
    }

    /**
     * @testWith ["16,409,10000,D,1,0,70000000,123456789,987654321,TEXT OF SUCH IMPORT", 0]
     *           ["16,409,10000,D,1,1,70000000,123456789,987654321,TEXT OF SUCH IMPORT", 1]
     *           ["16,409,10000,D,1,3,70000000,123456789,987654321,TEXT OF SUCH IMPORT", 3]
     *           ["16,409,10000,D,1,5,70000000,123456789,987654321,TEXT OF SUCH IMPORT", 5]
     *           ["16,409,10000,D,1,30,70000000,123456789,987654321,TEXT OF SUCH IMPORT", 30]
     *           ["16,409,10000,D,1,365,70000000,123456789,987654321,TEXT OF SUCH IMPORT", 365]
     */
    public function testFundsTypeDAvailabilityDayValid(
        string $line,
        int $expected
    ): void {
        $this->parser->pushLine($line);
        $this->assertTrue(
            array_key_exists(
                $expected,
                $this->parser['fundsType']['availability']
            )
        );
    }

    public function testFundsTypeDAvailabilityDayMissing(): void
    {
        $this->parser->pushLine('16,409,10000,D,1,,70000000,123456789,987654321,TEXT OF SUCH IMPORT');

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Availability in Days" cannot be omitted.');
        $this->parser['fundsType'];
    }

    /**
     * @testWith ["16,409,10000,D,1,a0,70000000,123456789,987654321,TEXT OF SUCH IMPORT"]
     *           ["16,409,10000,D,1,0b,70000000,123456789,987654321,TEXT OF SUCH IMPORT"]
     *           ["16,409,10000,D,1,-1,70000000,123456789,987654321,TEXT OF SUCH IMPORT"]
     *           ["16,409,10000,D,1,+1,70000000,123456789,987654321,TEXT OF SUCH IMPORT"]
     *           ["16,409,10000,D,1,0.5,70000000,123456789,987654321,TEXT OF SUCH IMPORT"]
     */
    public function testFundsTypeDAvailabilityDayInvalid(string $line): void
    {
        $this->parser->pushLine($line);

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Availability in Days" should be an unsigned integer.');
        $this->parser['fundsType'];
    }

    /**
     * @testWith ["16,409,10000,D,1,0,70000000,123456789,987654321,TEXT OF SUCH IMPORT", 70000000]
     *           ["16,409,10000,D,1,0,+70000000,123456789,987654321,TEXT OF SUCH IMPORT", 70000000]
     *           ["16,409,10000,D,1,0,-70000000,123456789,987654321,TEXT OF SUCH IMPORT", -70000000]
     *           ["16,409,10000,D,1,0,0,123456789,987654321,TEXT OF SUCH IMPORT", 0]
     */
    public function testFundsTypeDAvailabilityAmountValid(
        string $line,
        int $expected
    ): void {
        $this->parser->pushLine($line);
        $this->assertEquals(
            $expected,
            $this->parser['fundsType']['availability'][0]
        );
    }

    public function testFundsTypeDAvailabilityAmountMissing(): void
    {
        $this->parser->pushLine('16,409,10000,D,1,0,,123456789,987654321,TEXT OF SUCH IMPORT');

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Available Amount" cannot be omitted.');
        $this->parser['fundsType'];
    }

    /**
     * @testWith ["16,409,10000,D,1,0,foo,123456789,987654321,TEXT OF SUCH IMPORT"]
     *           ["16,409,10000,D,1,0,a70000000,123456789,987654321,TEXT OF SUCH IMPORT"]
     *           ["16,409,10000,D,1,0,70000000b,123456789,987654321,TEXT OF SUCH IMPORT"]
     *           ["16,409,10000,D,1,0,70000 000,123456789,987654321,TEXT OF SUCH IMPORT"]
     *           ["16,409,10000,D,1,0,700_00000,123456789,987654321,TEXT OF SUCH IMPORT"]
     *           ["16,409,10000,D,1,0,70000000.00,123456789,987654321,TEXT OF SUCH IMPORT"]
     *           ["16,409,10000,D,1,0,7+0000000,123456789,987654321,TEXT OF SUCH IMPORT"]
     *           ["16,409,10000,D,1,0,70-000000,123456789,987654321,TEXT OF SUCH IMPORT"]
     */
    public function testFundsTypeDAvailabilityAmountInvalid(string $line): void
    {
        $this->parser->pushLine($line);

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Available Amount" must be a signed or unsigned integer value.');
        $this->parser['fundsType'];
    }

    /**
     * @testWith ["16,409,,,123456789,987654321,TEXT OF SUCH IMPORT", "123456789"]
     *           ["16,409,,,abcdefghi,987654321,TEXT OF SUCH IMPORT", "abcdefghi"]
     *           ["16,409,,,abcd12345,987654321,TEXT OF SUCH IMPORT", "abcd12345"]
     *           ["16,409,,,12345abcd,987654321,TEXT OF SUCH IMPORT", "12345abcd"]
     *           ["16,409,,,000000001,987654321,TEXT OF SUCH IMPORT", "000000001"]
     *           ["16,409,,,thelengthofthebankreferencenumberisnotlimitedbutshouldprobablybenotmorethan76charactersbecausewhywouldyoueverneedmorethanthatquestionmark,987654321,TEXT OF SUCH IMPORT", "thelengthofthebankreferencenumberisnotlimitedbutshouldprobablybenotmorethan76charactersbecausewhywouldyoueverneedmorethanthatquestionmark"]
     */
    public function testBankReferenceNumberValid(
        string $line,
        string $expected
    ): void {
        $this->parser->pushLine($line);
        $this->assertEquals($expected, $this->parser['bankReferenceNumber']);
    }

    public function testBankReferenceNumberOptional(): void
    {
        $this->parser->pushLine('16,409,,0,,987654321,TEXT OF SUCH IMPORT');
        $this->assertNull($this->parser['bankReferenceNumber']);
    }

    /**
     * @testWith ["16,409,,, 123456789,987654321,TEXT OF SUCH IMPORT"]
     *           ["16,409,,,123456789 ,987654321,TEXT OF SUCH IMPORT"]
     *           ["16,409,,,1234_56789,987654321,TEXT OF SUCH IMPORT"]
     *           ["16,409,,,1234+56789,987654321,TEXT OF SUCH IMPORT"]
     *           ["16,409,,,1234-56789,987654321,TEXT OF SUCH IMPORT"]
     *           ["16,409,,,!@#$%^&*(),987654321,TEXT OF SUCH IMPORT"]
     *           ["16,409,,, ,987654321,TEXT OF SUCH IMPORT"]
     */
    public function testBankReferenceNumberInvalid(string $line): void
    {
        $this->parser->pushLine($line);

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Bank Reference Number" must be alpha-numeric when provided.');
        $this->parser['bankReferenceNumber'];
    }

    /**
     * @testWith ["16,409,,,123456789,987654321,TEXT OF SUCH IMPORT", "987654321"]
     *           ["16,409,,,123456789,rstuvwxyz,TEXT OF SUCH IMPORT", "rstuvwxyz"]
     *           ["16,409,,,123456789,98765wxyz,TEXT OF SUCH IMPORT", "98765wxyz"]
     *           ["16,409,,,123456789,wxyz98765,TEXT OF SUCH IMPORT", "wxyz98765"]
     *           ["16,409,,,123456789,000000009,TEXT OF SUCH IMPORT", "000000009"]
     *           ["16,409,,,123456789,thelengthofthecustomerreferencenumberisnotlimitedbutshouldprobablybenotmorethan76charactersbecausewhywouldyoueverneedmorethanthatquestionmark,TEXT OF SUCH IMPORT", "thelengthofthecustomerreferencenumberisnotlimitedbutshouldprobablybenotmorethan76charactersbecausewhywouldyoueverneedmorethanthatquestionmark"]
     */
    public function testCustomerReferenceNumberValid(
        string $line,
        string $expected
    ): void {
        $this->parser->pushLine($line);
        $this->assertEquals($expected, $this->parser['customerReferenceNumber']);
    }

    public function testCustomerReferenceNumberOptional(): void
    {
        $this->parser->pushLine('16,409,,0,123456789,,TEXT OF SUCH IMPORT');
        $this->assertNull($this->parser['customerReferenceNumber']);
    }

    /**
     * @testWith ["16,409,,,123456789, 987654321,TEXT OF SUCH IMPORT"]
     *           ["16,409,,,123456789,987654321 ,TEXT OF SUCH IMPORT"]
     *           ["16,409,,,123456789,9876_54321,TEXT OF SUCH IMPORT"]
     *           ["16,409,,,123456789,9876+54321,TEXT OF SUCH IMPORT"]
     *           ["16,409,,,123456789,9876-54321,TEXT OF SUCH IMPORT"]
     *           ["16,409,,,123456789,+_)(*&^%$,TEXT OF SUCH IMPORT"]
     *           ["16,409,,,123456789, ,TEXT OF SUCH IMPORT"]
     */
    public function testCustomerReferenceNumberInvalid(string $line): void
    {
        $this->parser->pushLine($line);

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Customer Reference Number" must be alpha-numeric when provided.');
        $this->parser['customerReferenceNumber'];
    }

    // TODO(zmd): public function testTextValid(): void {}
    // TODO(zmd): public function testTextMissing(): void {}
    // TODO(zmd): public function testTextInvalid(): void {}

}
