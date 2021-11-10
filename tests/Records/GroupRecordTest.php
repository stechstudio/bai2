<?php

namespace STS\Bai2\Records;

use PHPUnit\Framework\TestCase;

use STS\Bai2\Exceptions\MalformedInputException;

final class GroupRecordTest extends TestCase
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
        $record = new GroupRecord();

        foreach ($input as $line) {
            $record->parseLine($line);
        }

        $callable($record);
    }

    // -- test field getters ---------------------------------------------------

    // TODO(zmd): public function testGetUltimateReceiverIdentification(): void {}
    // TODO(zmd): public function testGetUltimateReceiverIdentificationDefaulted(): void {}

    // TODO(zmd): public function testGetOriginatorIdentification(): void {}
    // TODO(zmd): public function testGetOriginatorIdentificationMissing(): void {}

    // TODO(zmd): public function testGetGroupStatus(): void {}
    // TODO(zmd): public function testGetGroupStatusMissing(): void {}

    // TODO(zmd): public function testGetAsOfDate(): void {}
    // TODO(zmd): public function testGetAsOfDateMissing(): void {}

    // TODO(zmd): public function testGetAsOfTime(): void {}
    // TODO(zmd): public function testGetAsOfTimeDefaulted(): void {}

    // TODO(zmd): public function testGetCurrencyCode(): void {}
    // TODO(zmd): public function testGetCurrencyCodeDefaulted(): void {}

    // TODO(zmd): public function testGetAsOfDateModifier(): void {}
    // TODO(zmd): public function testGetAsOfDateModifierDefaulted(): void {}

    // TODO(zmd): public function testGetGroupControlTotal(): void {}
    // TODO(zmd): public function testGetGroupControlTotalMissing(): void {}

    // TODO(zmd): public function testGetNumberOfAccounts(): void {}
    // TODO(zmd): public function testGetNumberOfAccountsMissing(): void {}

    // TODO(zmd): public function testGetNumberOfRecords(): void {}
    // TODO(zmd): public function testGetNumberOfRecordsMissing(): void {}

    // -- test overall functionality -------------------------------------------

    // TODO(zmd): test ::toArray()

    // -- test overall error handling ------------------------------------------

    // TODO(zmd): public function testPhysicalRecordLengthEnforced(): void {}

    // TODO(zmd): public function testHeaderFieldAccessWhenHeaderNeverProcessed(): void {}

    // TODO(zmd): public function testTrailerFieldAccessWhenTrailerNeverProcessed(): void {}

    // TODO(zmd): public function testTryingToParseContinuationOutOfTurn(): void {}

    // TODO(zmd): public function testTryingToProcessChildLineBeforeChildInitialized(): void {}

    // TODO(zmd): public function testTryingToProcessIncompleteHeader(): void {}

    // TODO(zmd): public function testTryingToProcessIncompleteTrailer(): void {}

    // TODO(zmd): public function testTryingToProcessMalformedHeader(): void {}

    // TODO(zmd): public function testTryingToProcessMalformedTrailer(): void {}

}
