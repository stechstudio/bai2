<?php

namespace STS\Bai2\Records;

use PHPUnit\Framework\TestCase;

use STS\Bai2\Exceptions\MalformedInputException;

final class AccountRecordTest extends TestCase
{

    public function headerGettersProducer(): array
    {
        // TODO(zmd): finish implementing me
        return [[
        ]];
    }

    public function trailerGettersProducer(): array
    {
        // TODO(zmd): finish implementing me
        return [[
        ]];
    }

    public function inputLinesProducer(): array
    {
        // TODO(zmd): finish implementing me
        return [
            [[
            ]],
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

    protected function withRecord(array $input, callable $callable): void
    {
        $record = new AccountRecord();

        foreach ($input as $line) {
            $record->parseLine($line);
        }

        $callable($record);
    }

    // -- test field getters ---------------------------------------------------

    // TODO(zmd): public function testGetCustomerAccountNumber(): void {}
    // TODO(zmd): public function testGetCustomerAccountNumberMissing(): void {}

    // TODO(zmd): public function testGetCurrencyCode(): void {}
    // TODO(zmd): public function testGetCurrencyCodeDefaulted(): void {}

    // TODO(zmd): public function testGetTypeCode(): void {}
    // TODO(zmd): public function testGetTypeCodeDefaulted(): void {}

    // TODO(zmd): public function testGetAmount(): void {}
    // TODO(zmd): public function testGetAmountDefaulted(): void {}

    // TODO(zmd): public function testGetItemCount(): void {}
    // TODO(zmd): public function testGetItemCountDefaulted(): void {}

    // TODO(zmd): public function testGetFundsType(): void {}
    // TODO(zmd): public function testGetFundsTypeDefaulted(): void {}

    // TODO(zmd): public function testGetAccountControlTotal(): void {}
    // TODO(zmd): public function testGetAccountControlTotalMissing(): void {}

    // TODO(zmd): public function testGetNumberOfRecords(): void {}
    // TODO(zmd): public function testGetNumberOfRecordsMissing(): void {}

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
