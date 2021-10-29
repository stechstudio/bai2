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
                    'distributionOfAvailability' => null
                ]
            ],
            [
                '03,0975312468,,190,70000000,4,0/',
                [
                    'distributionOfAvailability' => '0'
                ]
            ],
            [
                '03,0975312468,,190,70000000,4,1/',
                [
                    'distributionOfAvailability' => '1'
                ]
            ],
            [
                '03,0975312468,,190,70000000,4,2/',
                [
                    'distributionOfAvailability' => '2'
                ]
            ],
            [
                '03,0975312468,,190,70000000,4,V,210909,0800/',
                [
                    'distributionOfAvailability' => '2',
                    'valueDate' => '210909',
                    'valueTime' => '0800'
                ]
            ],
            [
                '03,0975312468,,190,70000000,4,S,150000,100000,90000/',
                [
                    'distributionOfAvailability' => 'S',
                    'availability' => [
                        0 => 150000,
                        1 => 100000,
                        2 => 90000
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
        array $expectedSummaryAndStatusInformation
    ): void {
        $this->parser->pushLine($input);

        $this->assertEquals(
            $expectedSummaryAndStatusInformation,
            $this->parser['summaryAndStatusInformation'][0]['fundsType']
        );
    }

    // ----- record-specific field validation ----------------------------------

    // TODO(zmd): test "Customer Account Number" validation

    // TODO(zmd): test "Currency Code" validation

    // TODO(zmd): test "Type Code" validation (and composite sub-fields!)

    // TODO(zmd): test     "Amount" validation

    // TODO(zmd): test     "Item Count" validation

    // TODO(zmd): test     "Funds Type" validation (and composite sub-sub-fields!)

}
