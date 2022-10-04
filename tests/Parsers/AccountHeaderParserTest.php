<?php

declare(strict_types=1);

namespace STS\Bai2\Parsers;

use STS\Bai2\Tests\Parsers\RecordParserTestCase;

use STS\Bai2\Exceptions\ParseException;
use STS\Bai2\Exceptions\InvalidTypeException;

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

    public function testParseFromSingleLine(): void
    {
        $this->parser->pushLine(self::$fullRecordLine);

        $this->assertEquals('03', $this->parser['recordCode']);
        $this->assertEquals('0975312468', $this->parser['customerAccountNumber']);
        $this->assertEquals(null, $this->parser['currencyCode']);
        $this->assertEquals(
            [
                [
                    'typeCode' => '010',
                    'amount' => 500000
                ],
                [
                    'typeCode' => '190',
                    'amount' => 70000000,
                    'itemCount' => 4,
                    'fundsType' => [
                        'distributionOfAvailability' => '0'
                    ]
                ],
            ],
            $this->parser['summaryAndStatusInformation']
        );
    }

    public function testParseFromMultipleLines(): void
    {
        $this->parser->pushLine(self::$partialRecordLine);
        $this->parser->pushLine(self::$continuedRecordLine);

        $this->assertEquals('03', $this->parser['recordCode']);
        $this->assertEquals('0975312468', $this->parser['customerAccountNumber']);
        $this->assertEquals(null, $this->parser['currencyCode']);
        $this->assertEquals(
            [
                [
                    'typeCode' => '010',
                    'amount' => 500000
                ],
                [
                    'typeCode' => '190',
                    'amount' => 70000000,
                    'itemCount' => 4,
                    'fundsType' => [
                        'distributionOfAvailability' => '0'
                    ]
                ],
            ],
            $this->parser['summaryAndStatusInformation']
        );
    }

    public function testToArray(): void
    {
        $this->parser->pushLine(self::$fullRecordLine);

        $this->assertEquals(
            [
                'recordCode' => '03',
                'customerAccountNumber' => '0975312468',
                'currencyCode' => null,
                'summaryAndStatusInformation' => [
                    [
                        'typeCode' => '010',
                        'amount' => 500000
                    ],
                    [
                        'typeCode' => '190',
                        'amount' => 70000000,
                        'itemCount' => 4,
                        'fundsType' => [
                            'distributionOfAvailability' => '0'
                        ]
                    ],
                ]
            ],
            $this->parser->toArray()
        );
    }

    // ----- account-header-specific summary and status parsing shenanigans ----

    public function summaryAndStatusInformationVariationsProducer(): array
    {
        return [
            [
                '03,0975312468,,,,,/',
                []
            ],
            [
                '03,0975312468,,,,,,,,,/',
                []
            ],
            [
                '03,0975312468,,010,500000,,/',
                [
                    [
                        'typeCode' => '010',
                        'amount' => 500000
                    ],
                ]
            ],
            [
                '03,0975312468,,190,70000000,4,0/',
                [
                    [
                        'typeCode' => '190',
                        'amount' => 70000000,
                        'itemCount' => 4,
                        'fundsType' => [
                            'distributionOfAvailability' => '0'
                        ]
                    ],
                ]
            ],
            [
                '03,0975312468,,010,500000,,,190,70000000,4,0/',
                [
                    [
                        'typeCode' => '010',
                        'amount' => 500000
                    ],
                    [
                        'typeCode' => '190',
                        'amount' => 70000000,
                        'itemCount' => 4,
                        'fundsType' => [
                            'distributionOfAvailability' => '0'
                        ]
                    ],
                ]
            ],
            [
                '03,0975312468,,190,70000000,4,0,010,500000,,/',
                [
                    [
                        'typeCode' => '190',
                        'amount' => 70000000,
                        'itemCount' => 4,
                        'fundsType' => [
                            'distributionOfAvailability' => '0'
                        ]
                    ],
                    [
                        'typeCode' => '010',
                        'amount' => 500000
                    ],
                ]
            ],
            [
                '03,0975312468,,010,500000,,,024,133700,,,190,70000000,4,0,205,12345678,2,0,207,87654321,13,0/',
                [
                    [
                        'typeCode' => '010',
                        'amount' => 500000
                    ],
                    [
                        'typeCode' => '024',
                        'amount' => 133700
                    ],
                    [
                        'typeCode' => '190',
                        'amount' => 70000000,
                        'itemCount' => 4,
                        'fundsType' => [
                            'distributionOfAvailability' => '0'
                        ]
                    ],
                    [
                        'typeCode' => '205',
                        'amount' => 12345678,
                        'itemCount' => 2,
                        'fundsType' => [
                            'distributionOfAvailability' => '0'
                        ]
                    ],
                    [
                        'typeCode' => '207',
                        'amount' => 87654321,
                        'itemCount' => 13,
                        'fundsType' => [
                            'distributionOfAvailability' => '0'
                        ]
                    ],
                ]
            ],
            [
                '03,0975312468,,001,500000,,,099,133700,,,100,70000000,4,0,799,12345678,2,0/',
                [
                    [
                        'typeCode' => '001',
                        'amount' => 500000
                    ],
                    [
                        'typeCode' => '099',
                        'amount' => 133700
                    ],
                    [
                        'typeCode' => '100',
                        'amount' => 70000000,
                        'itemCount' => 4,
                        'fundsType' => [
                            'distributionOfAvailability' => '0'
                        ]
                    ],
                    [
                        'typeCode' => '799',
                        'amount' => 12345678,
                        'itemCount' => 2,
                        'fundsType' => [
                            'distributionOfAvailability' => '0'
                        ]
                    ],
                ]
            ],
            [
                '03,0975312468,,900,500000,,,919,133700,,,920,70000000,4,0,999,12345678,2,0/',
                [
                    [
                        'typeCode' => '900',
                        'amount' => 500000
                    ],
                    [
                        'typeCode' => '919',
                        'amount' => 133700
                    ],
                    [
                        'typeCode' => '920',
                        'amount' => 70000000,
                        'itemCount' => 4,
                        'fundsType' => [
                            'distributionOfAvailability' => '0'
                        ]
                    ],
                    [
                        'typeCode' => '999',
                        'amount' => 12345678,
                        'itemCount' => 2,
                        'fundsType' => [
                            'distributionOfAvailability' => '0'
                        ]
                    ],
                ]
            ],
            [
                '03,0975312468,,010,500000,,,190,,4,0,205,12345678,,0,207,87654321,13,/',
                [
                    [
                        'typeCode' => '010',
                        'amount' => 500000
                    ],
                    [
                        'typeCode' => '190',
                        'amount' => null,
                        'itemCount' => 4,
                        'fundsType' => [
                            'distributionOfAvailability' => '0'
                        ]
                    ],
                    [
                        'typeCode' => '205',
                        'amount' => 12345678,
                        'itemCount' => null,
                        'fundsType' => [
                            'distributionOfAvailability' => '0'
                        ]
                    ],
                    [
                        'typeCode' => '207',
                        'amount' => 87654321,
                        'itemCount' => 13,
                        'fundsType' => [
                            'distributionOfAvailability' => null
                        ]
                    ],
                ]
            ],
            [
                '03,0975312468,,010,+500000,,,015,-420000,,,024,133700,,/',
                [
                    [
                        'typeCode' => '010',
                        'amount' => 500000
                    ],
                    [
                        'typeCode' => '015',
                        'amount' => -420000
                    ],
                    [
                        'typeCode' => '024',
                        'amount' => 133700
                    ],
                ]
            ],
            [
                '03,0975312468,,'
                    . '190,70000000,4,D,7,0,35000000,1,17500000,2,8750000,3,4375000,4,2187500,5,1093750,6,1093750,'
                    . '205,12345678,2,V,211101,1141,'
                    . '207,87654321,13,S,60000000,17654321,10000000/',
                [
                    [
                        'typeCode' => '190',
                        'amount' => 70000000,
                        'itemCount' => 4,
                        'fundsType' => [
                            'distributionOfAvailability' => 'D',
                            'availability' => [
                                0 => 35000000,
                                1 => 17500000,
                                2 =>  8750000,
                                3 =>  4375000,
                                4 =>  2187500,
                                5 =>  1093750,
                                6 =>  1093750,
                            ]
                        ]
                    ],
                    [
                        'typeCode' => '205',
                        'amount' => 12345678,
                        'itemCount' => 2,
                        'fundsType' => [
                            'distributionOfAvailability' => 'V',
                            'valueDate' => '211101',
                            'valueTime' => '1141'
                        ]
                    ],
                    [
                        'typeCode' => '207',
                        'amount' => 87654321,
                        'itemCount' => 13,
                        'fundsType' => [
                            'distributionOfAvailability' => 'S',
                            'availability' => [
                                0 => 60000000,
                                1 => 17654321,
                                2 => 10000000,
                            ]
                        ]
                    ],
                ]
            ],
        ];
    }

    /**
     * @dataProvider summaryAndStatusInformationVariationsProducer
     */
    public function testSummaryAndStatusInformationVariations(
        string $input,
        array $expectedSummaryAndStatusInformation
    ): void {
        $this->parser->pushLine($input);

        $this->assertEquals(
            $expectedSummaryAndStatusInformation,
            $this->parser['summaryAndStatusInformation']
        );
    }

    public function fundsTypeVariationsProducer(): array
    {
        return [
            [
                '03,0975312468,,190,70000000,4,/',
                [
                    'distributionOfAvailability' => null,
                ]
            ],
            [
                '03,0975312468,,190,70000000,4,0/',
                [
                    'distributionOfAvailability' => '0',
                ]
            ],
            [
                '03,0975312468,,190,70000000,4,1/',
                [
                    'distributionOfAvailability' => '1',
                ]
            ],
            [
                '03,0975312468,,190,70000000,4,2/',
                [
                    'distributionOfAvailability' => '2',
                ]
            ],
            [
                '03,0975312468,,190,70000000,4,V,210909,0800/',
                [
                    'distributionOfAvailability' => 'V',
                    'valueDate' => '210909',
                    'valueTime' => '0800',
                ]
            ],
            [
                '03,0975312468,,190,70000000,4,V,210909,0000/',
                [
                    'distributionOfAvailability' => 'V',
                    'valueDate' => '210909',
                    'valueTime' => '0000',
                ]
            ],
            [
                '03,0975312468,,190,70000000,4,V,210909,2400/',
                [
                    'distributionOfAvailability' => 'V',
                    'valueDate' => '210909',
                    'valueTime' => '2400',
                ]
            ],
            [
                '03,0975312468,,190,70000000,4,V,210909,9999/',
                [
                    'distributionOfAvailability' => 'V',
                    'valueDate' => '210909',
                    'valueTime' => '9999',
                ]
            ],
            [
                '03,0975312468,,190,70000000,4,V,210909,/',
                [
                    'distributionOfAvailability' => 'V',
                    'valueDate' => '210909',
                    'valueTime' => null,
                ]
            ],
            [
                '03,0975312468,,190,70000000,4,S,150000,100000,90000/',
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
                '03,0975312468,,190,70000000,4,S,-150000,+100000,90000/',
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
                '03,0975312468,,190,70000000,4,D,3,0,150000,1,100000,2,90000/',
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
                '03,0975312468,,190,70000000,4,D,5,0,150000,1,100000,3,90000,5,70000,7,50000/',
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
                '03,0975312468,,190,70000000,4,D,30,'
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
                    . '150,15000/',
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
            $this->parser['summaryAndStatusInformation'][0]['fundsType']
        );
    }

    // ----- record-specific field validation ----------------------------------

    /**
     * @testWith ["03,123456,,190,70000000,4,0/", "123456"]
     *           ["03,000001,,190,70000000,4,0/", "000001"]
     *           ["03,abc123,,190,70000000,4,0/", "abc123"]
     *           ["03,123abc,,190,70000000,4,0/", "123abc"]
     *           ["03,abcxyz,,190,70000000,4,0/", "abcxyz"]
     */
    public function testCustomerAccountNumberValid(
        string $line,
        string $expected
    ): void {
        $this->parser->pushLine($line);
        $this->assertEquals(
            $expected,
            $this->parser['customerAccountNumber']
        );
    }

    public function testCustomerAccountNumberMissing(): void
    {
        $this->parser->pushLine('03,,,190,70000000,4,0/');

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Customer Account Number" cannot be omitted.');
        $this->parser['customerAccountNumber'];
    }

    /**
     * @testWith ["03, 123456,,190,70000000,4,0/"]
     *           ["03,123456 ,,190,70000000,4,0/"]
     *           ["03,123_456,,190,70000000,4,0/"]
     *           ["03,123+456,,190,70000000,4,0/"]
     *           ["03,123-456,,190,70000000,4,0/"]
     *           ["03,!@#$%^&,,190,70000000,4,0/"]
     *           ["03, ,,190,70000000,4,0/"]
     */
    public function testCustomerAccountNumberInvalid(string $line): void
    {
        $this->parser->pushLine($line);

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Customer Account Number" must be composed of one or more letters and/or numbers.');
        $this->parser['customerAccountNumber'];
    }

    /**
     * @testWith ["03,123456,USD,190,70000000,4,0/", "USD"]
     *           ["03,123456,ABC,190,70000000,4,0/", "ABC"]
     *           ["03,123456,XYZ,190,70000000,4,0/", "XYZ"]
     */
    public function testCurrencyCodeValid(string $line, string $expected): void
    {
        $this->parser->pushLine($line);
        $this->assertEquals($expected, $this->parser['currencyCode']);
    }

    public function testCurrencyCodeOptional(): void
    {
        $this->parser->pushLine('03,123456,,190,70000000,4,0/');
        $this->assertNull($this->parser['currencyCode']);
    }

    /**
     * @testWith ["03,123456, USD,190,70000000,4,0/"]
     *           ["03,123456,USD ,190,70000000,4,0/"]
     *           ["03,123456,AUSD,190,70000000,4,0/"]
     *           ["03,123456,UD,190,70000000,4,0/"]
     *           ["03,123456,123,190,70000000,4,0/"]
     *           ["03,123456,$$$,190,70000000,4,0/"]
     */
    public function testCurrencyCodeInvalid(string $line): void
    {
        $this->parser->pushLine($line);

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Currency Code" must be exactly 3 uppercase letters when provided.');
        $this->parser['currencyCode'];
    }

    /**
     * @testWith ["03,0975312468,,010,500000,,/", "010"]
     *           ["03,0975312468,,001,500000,,/", "001"]
     *           ["03,0975312468,,099,500000,,/", "099"]
     *           ["03,0975312468,,900,500000,,/", "900"]
     *           ["03,0975312468,,919,500000,,/", "919"]
     *           ["03,0975312468,,100,500000,,/", "100"]
     *           ["03,0975312468,,799,500000,,/", "799"]
     *           ["03,0975312468,,920,500000,,/", "920"]
     *           ["03,0975312468,,999,500000,,/", "999"]
     */
    public function testSummaryAndStatusInformationTypeCodeValid(string $line, string $expected): void
    {
        $this->parser->pushLine($line);
        $this->assertEquals($expected, $this->parser['summaryAndStatusInformation'][0]['typeCode']);
    }

    public function testSummaryAndStatusInformationTypeCodeOptional(): void {
        $this->parser->pushLine('03,0975312468,,,,,/');
        $this->assertEquals([], $this->parser['summaryAndStatusInformation']);
    }

    public function testSummaryAndStatusInformationTypeCodeMustBeDefaultedIfOmitted(): void {
        $this->parser->pushLine('03,0975312468,USD/');

        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Cannot access fields at the end of the buffer.');
        $this->parser['summaryAndStatusInformation'];
    }

    /**
     * @testWith ["03,0975312468,,ABC,500000,,/"]
     *           ["03,0975312468,,   ,500000,,/"]
     *           ["03,0975312468,,10,500000,,/"]
     *           ["03,0975312468,,1-1,500000,,/"]
     */
    public function testSummaryAndStatusInformationTypeCodeInvalid(string $line): void
    {
        $this->parser->pushLine($line);

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Type Code" must be composed of exactly three numerals when provided.');
        $this->parser['summaryAndStatusInformation'];
    }

    /**
     * @testWith ["03,0975312468,,800,500000,,/"]
     *           ["03,0975312468,,825,500000,,/"]
     *           ["03,0975312468,,899,500000,,/"]
     */
    public function testSummaryAndStatusInformationTypeCodeOutOfRange(string $line): void
    {
        $this->parser->pushLine($line);

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Type Code" was out outside the valid range for summary or status data.');
        $this->parser['summaryAndStatusInformation'];
    }

    /**
     * @testWith ["03,0975312468,,,5000,,/", "Amount"]
     *           ["03,0975312468,,,,4,/", "Item Count"]
     *           ["03,0975312468,,,,,0/", "Funds Type"]
     */
    public function testSummaryAndStatusInformationIfTypeCodeDefaultedSoTooMustBeAmountItemCountAndFundsType(
        string $line,
        string $expectedInvalidFieldLongName
    ): void {
        $this->parser->pushLine($line);

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage(
            'Invalid field type: "'
                . $expectedInvalidFieldLongName
                . '" must be defaulted since "Type Code" was defaulted.'
        );
        $this->parser['summaryAndStatusInformation'];
    }

    /**
     * @testWith ["03,0975312468,,010,500000,,/", 500000]
     *           ["03,0975312468,,010,+500000,,/", 500000]
     *           ["03,0975312468,,010,-500000,,/", -500000]
     *           ["03,0975312468,,010,0,,/", 0]
     */
    public function testSummaryAndStatusInformationStatusAmountValid(string $line, int $expected): void
    {
        $this->parser->pushLine($line);
        $this->assertEquals($expected, $this->parser['summaryAndStatusInformation'][0]['amount']);
    }

    public function testSummaryAndStatusInformationStatusAmountOptional(): void
    {
        $this->parser->pushLine('03,0975312468,,010,,,/');
        $this->assertNull($this->parser['summaryAndStatusInformation'][0]['amount']);
    }

    /**
     * @testWith ["03,0975312468,,010,foo,,/"]
     *           ["03,0975312468,,010,a500000,,/"]
     *           ["03,0975312468,,010,500000b,,/"]
     *           ["03,0975312468,,010,500 000,,/"]
     *           ["03,0975312468,,010,500_000,,/"]
     *           ["03,0975312468,,010,500.000,,/"]
     *           ["03,0975312468,,010,5+00000,,/"]
     *           ["03,0975312468,,010,50-0000,,/"]
     */
    public function testSummaryAndStatusInformationStatusAmountInvalid(string $line): void
    {
        $this->parser->pushLine($line);

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Amount" must be signed or unsigned integer when provided.');
        $this->parser['summaryAndStatusInformation'];
    }

    public function testSummaryAndStatusInformationStatusItemCountValid(): void
    {
        $this->parser->pushLine('03,0975312468,,010,5000,,/');

        $this->parser['summaryAndStatusInformation'];
        $this->assertArrayNotHasKey('itemCount', $this->parser['summaryAndStatusInformation'][0]);
    }

    public function testSummaryAndStatusInformationStatusItemCountInvalid(): void
    {
        $this->parser->pushLine('03,0975312468,,010,5000,4,/');

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Item Count" must be defaulted for status "Type Code".');
        $this->parser['summaryAndStatusInformation'];
    }

    public function testSummaryAndStatusInformationStatusFundsTypeValid(): void
    {
        $this->parser->pushLine('03,0975312468,,010,,,/');

        $this->parser['summaryAndStatusInformation'];
        $this->assertArrayNotHasKey('fundsType', $this->parser['summaryAndStatusInformation'][0]);
    }

    public function testSummaryAndStatusInformationStatusFundsTypeInvalid(): void
    {
        $this->parser->pushLine('03,0975312468,,010,5000,,0/');

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Funds Type" must be defaulted for status "Type Code".');
        $this->parser['summaryAndStatusInformation'];
    }

    /**
     * @testWith ["03,0975312468,,190,500000,4,0/", 500000]
     *           ["03,0975312468,,190,+500000,4,0/", 500000]
     *           ["03,0975312468,,190,0,4,0/", 0]
     */
    public function testSummaryAndStatusInformationSummaryAmountValid(string $line, int $expected): void
    {
        $this->parser->pushLine($line);
        $this->assertEquals($expected, $this->parser['summaryAndStatusInformation'][0]['amount']);
    }

    public function testSummaryAndStatusInformationSummaryAmountOptional(): void
    {
        $this->parser->pushLine('03,0975312468,,190,,4,0/');
        $this->assertNull($this->parser['summaryAndStatusInformation'][0]['amount']);
    }

    /**
     * @testWith ["03,0975312468,,190,foo,4,0/"]
     *           ["03,0975312468,,190,a500000,4,0/"]
     *           ["03,0975312468,,190,500000b,4,0/"]
     *           ["03,0975312468,,190,500 000,4,0/"]
     *           ["03,0975312468,,190,500_000,4,0/"]
     *           ["03,0975312468,,190,500.000,4,0/"]
     *           ["03,0975312468,,190,5+00000,4,0/"]
     *           ["03,0975312468,,190,-500000,4,0/"]
     */
    public function testSummaryAndStatusInformationSummaryAmountInvalid(string $line): void
    {
        $this->parser->pushLine($line);

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Amount" must be positive integer when provided.');
        $this->parser['summaryAndStatusInformation'];
    }

    /**
     * @testWith ["03,0975312468,,190,500000,4,0/"]
     *           ["03,0975312468,,190,500000,04,0/"]
     *           ["03,0975312468,,190,500000,+4,0/"]
     *           ["03,0975312468,,190,500000,+04,0/"]
     */
    public function testSummaryAndStatusInformationSummaryItemCountValid(string $line): void
    {
        $this->parser->pushLine($line);
        $this->assertEquals(4, $this->parser['summaryAndStatusInformation'][0]['itemCount']);
    }

    public function testSummaryAndStatusInformationSummaryItemCountOptional(): void
    {
        $this->parser->pushLine('03,0975312468,,190,500000,,0/');
        $this->assertNull($this->parser['summaryAndStatusInformation'][0]['itemCount']);
    }

    /**
     * @testWith ["03,0975312468,,190,500000,-4,0/"]
     *           ["03,0975312468,,190,500000,4.0,0/"]
     *           ["03,0975312468,,190,500000,a4,0/"]
     *           ["03,0975312468,,190,500000,4b,0/"]
     *           ["03,0975312468,,190,500000,four,0/"]
     */
    public function testSummaryAndStatusInformationSummaryItemCountInvalid(string $line): void
    {
        $this->parser->pushLine($line);

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Item Count" must be positive integer when provided.');
        $this->parser['summaryAndStatusInformation'];
    }

    /**
     * @testWith ["03,0975312468,,190,70000000,4,0/", "0"]
     *           ["03,0975312468,,190,70000000,4,1/", "1"]
     *           ["03,0975312468,,190,70000000,4,2/", "2"]
     *           ["03,0975312468,,190,70000000,4,V,210909,0800/", "V"]
     *           ["03,0975312468,,190,70000000,4,S,150000,100000,90000/", "S"]
     *           ["03,0975312468,,190,70000000,4,D,3,0,150000,1,100000,2,90000/", "D"]
     */
    public function testSummaryAndStatusInformationSummaryFundsTypeDistributionOfAvailabilityValid(
        string $line,
        string $expected
    ): void {
        $this->parser->pushLine($line);
        $this->assertEquals(
            $expected,
            $this->parser['summaryAndStatusInformation'][0]['fundsType']['distributionOfAvailability']
        );
    }

    public function testSummaryAndStatusInformationSummaryFundsTypeDistributionOfAvailabilityOptional(): void
    {
        $this->parser->pushLine('03,0975312468,,190,70000000,4,/');
        $this->assertNull(
            $this->parser['summaryAndStatusInformation'][0]['fundsType']['distributionOfAvailability']
        );
    }

    /**
     * @testWith ["03,0975312468,,190,70000000,4,-1/"]
     *           ["03,0975312468,,190,70000000,4,3/"]
     *           ["03,0975312468,,190,70000000,4,X/"]
     *           ["03,0975312468,,190,70000000,4,_/"]
     *           ["03,0975312468,,190,70000000,4,one/"]
     *           ["03,0975312468,,190,70000000,4,00/"]
     *           ["03,0975312468,,190,70000000,4,01/"]
     *           ["03,0975312468,,190,70000000,4,02/"]
     *           ["03,0975312468,,190,70000000,4,v,210909,0800/"]
     *           ["03,0975312468,,190,70000000,4,s,150000,100000,90000/"]
     *           ["03,0975312468,,190,70000000,4,d,3,0,150000,1,100000,2,90000/"]
     */
    public function testSummaryAndStatusInformationSummaryFundsTypeDistributionOfAvailabilityInvalid(
        string $line
    ): void {
        $this->parser->pushLine($line);

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage(
            'Invalid field type: "Distribution of Availability" for "Funds Type" must be one of "0", "1", "2", "V", "S", "D", or "Z" when provided.'
        );
        $this->parser['summaryAndStatusInformation'];
    }

    /**
     * @testWith ["03,0975312468,,190,70000000,4,V,210909,0800/", "210909"]
     *           ["03,0975312468,,190,70000000,4,V,000000,0800/", "000000"]
     *           ["03,0975312468,,190,70000000,4,V,999999,0800/", "999999"]
     */
    public function testSummaryAndStatusInformationSummaryFundsTypeValueDateValid(
        string $line,
        string $expected
    ): void {
        $this->parser->pushLine($line);
        $this->assertEquals(
            $expected,
            $this->parser['summaryAndStatusInformation'][0]['fundsType']['valueDate']
        );
    }

    public function testSummaryAndStatusInformationSummaryFundsTypeValueDateMissing(): void
    {
        $this->parser->pushLine('03,0975312468,,190,70000000,4,V,,0800/');

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Value Dated Date" cannot be omitted.');
        $this->parser['summaryAndStatusInformation'];
    }

    /**
     * @testWith ["03,0975312468,,190,70000000,4,V,a10909,0800/"]
     *           ["03,0975312468,,190,70000000,4,V,21090b,0800/"]
     *           ["03,0975312468,,190,70000000,4,V,20210909,0800/"]
     *           ["03,0975312468,,190,70000000,4,V,21-09-09,0800/"]
     *           ["03,0975312468,,190,70000000,4,V,9-Sep 2021,0800/"]
     */
    public function testSummaryAndStatusInformationSummaryFundsTypeValueDateInvalid(string $line): void
    {
        $this->parser->pushLine($line);

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Value Dated Date" must be exactly 6 numerals (YYMMDD).');
        $this->parser['summaryAndStatusInformation'];
    }

    /**
     * @testWith ["03,0975312468,,190,70000000,4,V,210909,0800/", "0800"]
     *           ["03,0975312468,,190,70000000,4,V,210909,0000/", "0000"]
     *           ["03,0975312468,,190,70000000,4,V,210909,2400/", "2400"]
     *           ["03,0975312468,,190,70000000,4,V,210909,9999/", "9999"]
     */
    public function testSummaryAndStatusInformationSummaryFundsTypeValueTimeValid(
        string $line,
        string $expected
    ): void {
        $this->parser->pushLine($line);
        $this->assertEquals(
            $expected,
            $this->parser['summaryAndStatusInformation'][0]['fundsType']['valueTime']
        );
    }

    public function testSummaryAndStatusInformationSummaryFundsTypeValueTimeOptional(): void
    {
        $this->parser->pushLine('03,0975312468,,190,70000000,4,V,210909,/');
        $this->assertNull(
            $this->parser['summaryAndStatusInformation'][0]['fundsType']['valueTime']
        );
    }

    /**
     * @testWith ["03,0975312468,,190,70000000,4,V,210909,a800/"]
     *           ["03,0975312468,,190,70000000,4,V,210909,080b/"]
     *           ["03,0975312468,,190,70000000,4,V,210909,08:00/"]
     *           ["03,0975312468,,190,70000000,4,V,210909,800/"]
     */
    public function testSummaryAndStatusInformationSummaryFundsTypeValueTimeInvalid(string $line): void
    {
        $this->parser->pushLine($line);

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Value Dated Time" must be exactly 4 numerals (HHMM) when provided.');
        $this->parser['summaryAndStatusInformation'];
    }

    /**
     * @testWith ["03,0975312468,,190,70000000,4,S,150000,100000,90000/", 150000]
     *           ["03,0975312468,,190,70000000,4,S,+150000,100000,90000/", 150000]
     *           ["03,0975312468,,190,70000000,4,S,-150000,100000,90000/", -150000]
     */
    public function testSummaryAndStatusInformationSummaryFundsTypeSImmediateAvailabilityValid(
        string $line,
        int $expected
    ): void {
        $this->parser->pushLine($line);
        $this->assertEquals(
            $expected,
            $this->parser['summaryAndStatusInformation'][0]['fundsType']['availability'][0]
        );
    }

    public function testSummaryAndStatusInformationSummaryFundsTypeSImmediateAvailabilityMissing(): void
    {
        $this->parser->pushLine('03,0975312468,,190,70000000,4,S,,100000,90000/');

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Immediate Availability" cannot be omitted.');
        $this->parser['summaryAndStatusInformation'];
    }

    /**
     * @testWith ["03,0975312468,,190,70000000,4,S,a150000,100000,90000/"]
     *           ["03,0975312468,,190,70000000,4,S,150000b,100000,90000/"]
     *           ["03,0975312468,,190,70000000,4,S,150_000,100000,90000/"]
     *           ["03,0975312468,,190,70000000,4,S,150000.00,100000,90000/"]
     */
    public function testSummaryAndStatusInformationSummaryFundsTypeSImmediateAvailabilityInvalid(
        string $line
    ): void {
        $this->parser->pushLine($line);

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Immediate Availability" must be a signed or unsigned integer value.');
        $this->parser['summaryAndStatusInformation'];
    }

    /**
     * @testWith ["03,0975312468,,190,70000000,4,S,150000,100000,90000/", 100000]
     *           ["03,0975312468,,190,70000000,4,S,150000,+100000,90000/", 100000]
     *           ["03,0975312468,,190,70000000,4,S,150000,-100000,90000/", -100000]
     */
    public function testSummaryAndStatusInformationSummaryFundsTypeSOneDayAvailabilityValid(
        string $line,
        int $expected
    ): void {
        $this->parser->pushLine($line);
        $this->assertEquals(
            $expected,
            $this->parser['summaryAndStatusInformation'][0]['fundsType']['availability'][1]
        );
    }

    public function testSummaryAndStatusInformationSummaryFundsTypeSOneDayAvailabilityMissing(): void
    {
        $this->parser->pushLine('03,0975312468,,190,70000000,4,S,150000,,90000/');

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "One-day Availability" cannot be omitted.');
        $this->parser['summaryAndStatusInformation'];
    }

    /**
     * @testWith ["03,0975312468,,190,70000000,4,S,150000,a100000,90000/"]
     *           ["03,0975312468,,190,70000000,4,S,150000,100000b,90000/"]
     *           ["03,0975312468,,190,70000000,4,S,150000,100_000,90000/"]
     *           ["03,0975312468,,190,70000000,4,S,150000,100000.00,90000/"]
     */
    public function testSummaryAndStatusInformationSummaryFundsTypeSOneDayAvailabilityInvalid(
        string $line
    ): void {
        $this->parser->pushLine($line);

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "One-day Availability" must be a signed or unsigned integer value.');
        $this->parser['summaryAndStatusInformation'];
    }

    /**
     * @testWith ["03,0975312468,,190,70000000,4,S,150000,100000,90000/", 90000]
     *           ["03,0975312468,,190,70000000,4,S,150000,100000,+90000/", 90000]
     *           ["03,0975312468,,190,70000000,4,S,150000,100000,-90000/", -90000]
     */
    public function testSummaryAndStatusInformationSummaryFundsTypeSTwoOrMoreDayAvailabilityValid(
        string $line,
        int $expected
    ): void {
        $this->parser->pushLine($line);
        $this->assertEquals(
            $expected,
            $this->parser['summaryAndStatusInformation'][0]['fundsType']['availability'][2]
        );
    }

    public function testSummaryAndStatusInformationSummaryFundsTypeSTwoOrMoreDayAvailabilityMissing(): void
    {
        $this->parser->pushLine('03,0975312468,,190,70000000,4,S,150000,100000,/');

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Two-or-more Day Availability" cannot be omitted.');
        $this->parser['summaryAndStatusInformation'];
    }

    /**
     * @testWith ["03,0975312468,,190,70000000,4,S,150000,100000,a90000/"]
     *           ["03,0975312468,,190,70000000,4,S,150000,100000,90000b/"]
     *           ["03,0975312468,,190,70000000,4,S,150000,100000,90_000/"]
     *           ["03,0975312468,,190,70000000,4,S,150000,100000,90000.00/"]
     */
    public function testSummaryAndStatusInformationSummaryFundsTypeSTwoOrMoreDayAvailabilityInvalid(
        string $line
    ): void {
        $this->parser->pushLine($line);

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Two-or-more Day Availability" must be a signed or unsigned integer value.');
        $this->parser['summaryAndStatusInformation'];
    }

    /**
     * @testWith ["03,0975312468,,190,70000000,4,D,0/", 0]
     *           ["03,0975312468,,190,70000000,4,D,1,0,70000000/", 1]
     *           ["03,0975312468,,190,70000000,4,D,3,0,50000000,1,15000000,2,5000000/", 3]
     *           ["03,0975312468,,190,70000000,4,D,5,0,150000,1,100000,3,90000,5,70000,7,50000/", 5]
     *           ["03,0975312468,,190,70000000,4,D,30,5,150000,10,145000,15,140000,20,135000,25,130000,30,125000,35,120000,40,125000,45,120000,50,115000,55,110000,60,105000,65,100000,70,95000,75,90000,80,85000,85,80000,90,75000,95,70000,100,65000,105,60000,110,55000,115,50000,120,45000,125,40000,130,35000,135,30000,140,25000,145,20000,150,15000/", 30]
     */
    public function testSummaryAndStatusInformationSummaryFundsTypeDLengthValid(
        string $line,
        int $expected
    ): void {
        $this->parser->pushLine($line);
        $this->assertEquals(
            $expected,
            count($this->parser['summaryAndStatusInformation'][0]['fundsType']['availability'])
        );
    }

    public function testSummaryAndStatusInformationSummaryFundsTypeDLengthMissing(): void
    {
        $this->parser->pushLine('03,0975312468,,190,70000000,4,D,,0,70000000/');

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Number of Distributions" cannot be omitted.');
        $this->parser['summaryAndStatusInformation'];
    }

    /**
     * @testWith ["03,0975312468,,190,70000000,4,D,a1,0,70000000/"]
     *           ["03,0975312468,,190,70000000,4,D,1b,0,70000000/"]
     *           ["03,0975312468,,190,70000000,4,D,-1,0,70000000/"]
     *           ["03,0975312468,,190,70000000,4,D,+1,0,70000000/"]
     *           ["03,0975312468,,190,70000000,4,D,1.0,0,70000000/"]
     */
    public function testSummaryAndStatusInformationSummaryFundsTypeDLengthInvalid(string $line): void
    {
        $this->parser->pushLine($line);

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Number of Distributions" should be an unsigned integer.');
        $this->parser['summaryAndStatusInformation'];
    }

    /**
     * @testWith ["03,0975312468,,190,70000000,4,D,1,0,70000000/", 0]
     *           ["03,0975312468,,190,70000000,4,D,1,1,70000000/", 1]
     *           ["03,0975312468,,190,70000000,4,D,1,3,70000000/", 3]
     *           ["03,0975312468,,190,70000000,4,D,1,5,70000000/", 5]
     *           ["03,0975312468,,190,70000000,4,D,1,30,70000000/", 30]
     *           ["03,0975312468,,190,70000000,4,D,1,365,70000000/", 365]
     */
    public function testSummaryAndStatusInformationSummaryFundsTypeDAvailabilityDayValid(
        string $line,
        int $expected
    ): void {
        $this->parser->pushLine($line);
        $this->assertTrue(
            array_key_exists(
                $expected,
                $this->parser['summaryAndStatusInformation'][0]['fundsType']['availability']
            )
        );
    }

    public function testSummaryAndStatusInformationSummaryFundsTypeDAvailabilityDayMissing(): void
    {
        $this->parser->pushLine('03,0975312468,,190,70000000,4,D,1,,70000000/');

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Availability in Days" cannot be omitted.');
        $this->parser['summaryAndStatusInformation'];
    }

    /**
     * @testWith ["03,0975312468,,190,70000000,4,D,1,a0,70000000/"]
     *           ["03,0975312468,,190,70000000,4,D,1,0b,70000000/"]
     *           ["03,0975312468,,190,70000000,4,D,1,-1,70000000/"]
     *           ["03,0975312468,,190,70000000,4,D,1,+1,70000000/"]
     *           ["03,0975312468,,190,70000000,4,D,1,0.5,70000000/"]
     */
    public function testSummaryAndStatusInformationSummaryFundsTypeDAvailabilityDayInvalid(
        string $line
    ): void {
        $this->parser->pushLine($line);

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Availability in Days" should be an unsigned integer.');
        $this->parser['summaryAndStatusInformation'];
    }

    /**
     * @testWith ["03,0975312468,,190,70000000,4,D,1,0,70000000/", 70000000]
     *           ["03,0975312468,,190,70000000,4,D,1,0,+70000000/", 70000000]
     *           ["03,0975312468,,190,70000000,4,D,1,0,-70000000/", -70000000]
     *           ["03,0975312468,,190,70000000,4,D,1,0,0/", 0]
     */
    public function testSummaryAndStatusInformationSummaryFundsTypeDAvailabilityAmountValid(
        string $line,
        int $expected
    ): void {
        $this->parser->pushLine($line);
        $this->assertEquals(
            $expected,
            $this->parser['summaryAndStatusInformation'][0]['fundsType']['availability'][0]
        );
    }

    public function testSummaryAndStatusInformationSummaryFundsTypeDAvailabilityAmountMissing(): void
    {
        $this->parser->pushLine('03,0975312468,,190,70000000,4,D,1,0,/');

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Available Amount" cannot be omitted.');
        $this->parser['summaryAndStatusInformation'];
    }

    /**
     * @testWith ["03,0975312468,,190,70000000,4,D,1,0,foo/"]
     *           ["03,0975312468,,190,70000000,4,D,1,0,a70000000/"]
     *           ["03,0975312468,,190,70000000,4,D,1,0,70000000b/"]
     *           ["03,0975312468,,190,70000000,4,D,1,0,70000 000/"]
     *           ["03,0975312468,,190,70000000,4,D,1,0,700_00000/"]
     *           ["03,0975312468,,190,70000000,4,D,1,0,70000000.00/"]
     *           ["03,0975312468,,190,70000000,4,D,1,0,7+0000000/"]
     *           ["03,0975312468,,190,70000000,4,D,1,0,70-000000/"]
     */
    public function testSummaryAndStatusInformationSummaryFundsTypeDAvailabilityAmountInvalid(string $line): void
    {
        $this->parser->pushLine($line);

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Available Amount" must be a signed or unsigned integer value.');
        $this->parser['summaryAndStatusInformation'];
    }

}
