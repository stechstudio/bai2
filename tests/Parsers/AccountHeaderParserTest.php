<?php

namespace STS\Bai2\Parsers;

use STS\Bai2\Tests\Parsers\RecordParserTestCase;

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

    // TODO(zmd): public function testCustomerAccountNumberValid(): void {}
    // TODO(zmd): public function testCustomerAccountNumberMissing(): void {}
    // TODO(zmd): public function testCustomerAccountNumberInvalid(): void {}

    // TODO(zmd): public function testCurrencyCodeValid(): void {}
    // TODO(zmd): public function testCurrencyCodeMissing(): void {}
    // TODO(zmd): public function testCurrencyCodeInvalid(): void {}

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
        $this->parser->pushLine("03,0975312468,,,,,/");
        $this->assertEquals([], $this->parser['summaryAndStatusInformation']);
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
     * @testWith ["03,0975312468,,,5000,,/", "Amount"]
     *           ["03,0975312468,,,,4,/", "Item Count"]
     *           ["03,0975312468,,,,,0/", "Funds Type"]
     */
    public function testSummaryAndStatusInformationIfTypeCodeDefaultedSoTooMustBeAmountItemCountAndFundsType(string $line, string $expectedInvalidFieldLongName): void
    {
        $this->parser->pushLine($line);

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage(
            'Invalid field type: "'
                . $expectedInvalidFieldLongName
                . '" must be defaulted since "Type Code" was defaulted.'
        );
        $this->parser['summaryAndStatusInformation'];
    }

    // TODO(zmd): public function testSummaryAndStatusInformationAmountValid(): void {}
    // TODO(zmd): public function testSummaryAndStatusInformationAmountMissing(): void {}
    // TODO(zmd): public function testSummaryAndStatusInformationAmountInvalid(): void {}

    // TODO(zmd): public function testSummaryAndStatusInformationItemCountValid(): void {}
    // TODO(zmd): public function testSummaryAndStatusInformationItemCountMissing(): void {}
    // TODO(zmd): public function testSummaryAndStatusInformationItemCountInvalid(): void {}

    // TODO(zmd): public function testSummaryAndStatusInformationFundsTypeDistributionOfAvailabilityValid(): void {}
    // TODO(zmd): public function testSummaryAndStatusInformationFundsTypeDistributionOfAvailabilityMissing(): void {}
    // TODO(zmd): public function testSummaryAndStatusInformationFundsTypeDistributionOfAvailabilityInvalid(): void {}

    // TODO(zmd): public function testSummaryAndStatusInformationFundsTypeValueDateValid(): void {}
    // TODO(zmd): public function testSummaryAndStatusInformationFundsTypeValueDateMissing(): void {}
    // TODO(zmd): public function testSummaryAndStatusInformationFundsTypeValueDateInvalid(): void {}

    // TODO(zmd): public function testSummaryAndStatusInformationFundsTypeValueTimeValid(): void {}
    // TODO(zmd): public function testSummaryAndStatusInformationFundsTypeValueTimeMissing(): void {}
    // TODO(zmd): public function testSummaryAndStatusInformationFundsTypeValueTimeInvalid(): void {}

    // TODO(zmd): public function testSummaryAndStatusInformationFundsTypeAvailabilityValid(): void {}
    // TODO(zmd): public function testSummaryAndStatusInformationFundsTypeAvailabilityInvalid(): void {}
    // TODO(zmd): public function testSummaryAndStatusInformationFundsTypeAvailabilityMissing(): void {}

}
