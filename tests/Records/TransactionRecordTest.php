<?php

namespace STS\Bai2\Records;

use PHPUnit\Framework\TestCase;

use STS\Bai2\Exceptions\MalformedInputException;

final class TransactionRecordTest extends TestCase
{

    public function gettersProducer(): array
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
        $record = new TransactionRecord();

        foreach ($input as $line) {
            $record->parseLine($line);
        }

        $callable($record);
    }

    // -- test field getters ---------------------------------------------------

    // TODO(zmd): public function testGetTypeCode(): void {}
    // TODO(zmd): public function testGetTypeCodeMissing(): void {}

    // TODO(zmd): public function testGetAmount(): void {}
    // TODO(zmd): public function testGetAmountDefaulted(): void {}

    // TODO(zmd): public function testGetFundsType(): void {}
    // TODO(zmd): public function testGetFundsTypeDefaulted(): void {}

    // TODO(zmd): public function testGetBankReferenceNumber(): void {}
    // TODO(zmd): public function testGetBankReferenceNumberDefaulted(): void {}

    // TODO(zmd): public function testGetCustomerReferenceNumber(): void {}
    // TODO(zmd): public function testGetCustomerReferenceNumberDefaulted(): void {}

    // TODO(zmd): public function testGetText(): void {}
    // TODO(zmd): public function testGetTextDefaulted(): void {}

    // -- test overall functionality -------------------------------------------

    // TODO(zmd): test ::toArray()

    // -- test overall error handling ------------------------------------------

    // TODO(zmd): public function testPhysicalRecordLengthEnforced(): void {}

    // TODO(zmd): public function testFieldAccessWhenRecordNeverProcessed(): void {}

    // TODO(zmd): public function testTryingToParseContinuationOutOfTurn(): void {}

    // TODO(zmd): public function testTryingToProcessUnknownRecordType(): void {}

    // TODO(zmd): public function testTryingToProcessIncompleteRecord(): void {}

    // TODO(zmd): public function testTryingToProcessMalformedRecord(): void {}

}
