<?php

declare(strict_types=1);

namespace STS\Bai2\Records;

use PHPUnit\Framework\TestCase;

use STS\Bai2\Exceptions\MalformedInputException;

final class AccountRecordTest extends TestCase
{

    public function headerGettersProducer(): array
    {
        // TODO(zmd): finish implementing me
        return [
            [],
        ];
    }

    public function trailerGettersProducer(): array
    {
        // TODO(zmd): finish implementing me
        return [
            [],
        ];
    }

    public function inputLinesProducer(): array
    {
        return [
            [[
                '03,0001,USD,'
                    . '010,500000,,,'
                    . '190,70000000,4,0/',
                '16,409,10000,0,123456789,987654321,SOME TEXT',
                '16,409,10000,2,123456789,987654321,SOME TEXT',
                '49,70520000,4/',
            ]],
            // TODO(zmd): finish implementing me
        ];
    }

    public function inputLinesTooLongProducer(): array
    {
        // TODO(zmd): finish implementing me
        return [
            [[
            ]],
        ];
    }

    protected function withRecord(
        array $input,
        ?int $physicalRecordLength,
        callable $callable
    ): void {
        $record = new AccountRecord(physicalRecordLength: $physicalRecordLength);

        foreach ($input as $line) {
            $record->parseLine($line);
        }

        $callable($record);
    }

    // -- test field getters ---------------------------------------------------

    /**
     * @dataProvider inputLinesProducer
     */
    public function testGetCustomerAccountNumber(array $inputLines): void
    {
        $this->withRecord($inputLines, null, function ($accountRecord) {
            $this->assertEquals(
                '0001',
                $accountRecord->getCustomerAccountNumber()
            );
        });
    }

    public function testGetCustomerAccountNumberMissing(): void
    {
        $accountRecord = new AccountRecord(physicalRecordLength: null);
        $accountRecord->parseLine('03,,USD,010,500000,,,190,70000000,4,0/');

        $this->expectException(MalformedInputException::class);
        $this->expectExceptionMessage('Encountered issue trying to parse Account Identifier and Summary Status Field. Invalid field type: ');
        $accountRecord->getCustomerAccountNumber();
    }

    /**
     * @dataProvider inputLinesProducer
     */
    public function testGetCurrencyCode(array $inputLines): void
    {
        $this->withRecord($inputLines, null, function ($accountRecord) {
            $this->assertEquals('USD', $accountRecord->getCurrencyCode());
        });
    }

    public function testGetCurrencyCodeDefaulted(): void
    {
        $accountRecord = new AccountRecord(physicalRecordLength: null);
        $accountRecord->parseLine('03,0001,,010,500000,,,190,70000000,4,0/');

        $this->assertNull($accountRecord->getCurrencyCode());
    }

    /**
     * @dataProvider inputLinesProducer
     */
    public function testGetSummaryAndStatusInformation(array $inputLines): void
    {
        $this->withRecord($inputLines, null, function ($accountRecord) {
            $this->assertEquals(
                [
                    [
                        'typeCode' => '010',
                        'amount' => 500000,
                    ],
                    [
                        'typeCode' => '190',
                        'amount' => 70000000,
                        'itemCount' => 4,
                        'fundsType' => [
                            'distributionOfAvailability' => '0'
                        ],
                    ],
                ],
                $accountRecord->getSummaryAndStatusInformation()
            );
        });
    }

    public function testGetSummaryAndStatusInformationDefaulted(): void
    {
        $accountRecord = new AccountRecord(physicalRecordLength: null);
        $accountRecord->parseLine('03,0001,USD,,,,/');

        $this->assertEquals([], $accountRecord->getSummaryAndStatusInformation());
    }

    /**
     * @dataProvider inputLinesProducer
     */
    public function testGetAccountControlTotal(array $inputLines): void
    {
        $this->withRecord($inputLines, null, function ($accountRecord) {
            $this->assertEquals(
                70520000,
                $accountRecord->getAccountControlTotal()
            );
        });
    }

    public function testGetAccountControlTotalMissing(): void
    {
        $accountRecord = new AccountRecord(physicalRecordLength: null);
        $accountRecord->parseLine('03,0001,USD,010,500000,,,190,70000000,4,0/');
        $accountRecord->parseLine('49,,2/');

        $this->expectException(MalformedInputException::class);
        $this->expectExceptionMessage('Encountered issue trying to parse Account Trailer Field. Invalid field type: ');
        $accountRecord->getAccountControlTotal();
    }

    /**
     * @dataProvider inputLinesProducer
     */
    public function testGetNumberOfRecords(array $inputLines): void
    {
        $this->withRecord($inputLines, null, function ($accountRecord) {
            $this->assertEquals(4, $accountRecord->getNumberOfRecords());
        });
    }

    public function testGetNumberOfRecordsMissing(): void
    {
        $accountRecord = new AccountRecord(physicalRecordLength: null);
        $accountRecord->parseLine('03,0001,USD,010,500000,,,190,70000000,4,0/');
        $accountRecord->parseLine('49,70520000,/');

        $this->expectException(MalformedInputException::class);
        $this->expectExceptionMessage('Encountered issue trying to parse Account Trailer Field. Invalid field type: ');
        $accountRecord->getNumberOfRecords();
    }

    // -- test overall functionality -------------------------------------------

    // TODO(zmd): test ::toArray()

    // -- test overall error handling ------------------------------------------

    // TODO(zmd): public function testPhysicalRecordLengthEnforced(): void {}

    // TODO(zmd): public function testHeaderFieldAccessWhenHeaderNeverProcessed(): void {}

    // TODO(zmd): public function testTrailerFieldAccessWhenTrailerNeverProcessed(): void {}

    // TODO(zmd): public function testTryingToParseContinuationOutOfTurn(): void {}

    // TODO(zmd): public function testTryingToProcessUnknownRecordType(): void {}

    // TODO(zmd): public function testTryingToProcessIncompleteHeader(): void {}

    // TODO(zmd): public function testTryingToProcessIncompleteTrailer(): void {}

    // TODO(zmd): public function testTryingToProcessMalformedHeader(): void {}

    // TODO(zmd): public function testTryingToProcessMalformedTrailer(): void {}

}
