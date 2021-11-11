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
        return [
            [[
                '02,abc,def,1,212209,0944,USD,2/',
                '03,0001,,,,,/',
                '49,0,2/',
                '03,0002,,,,,/',
                '49,0,2/',
                '98,10000,1,6/',
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

    protected function withRecord(
        array $input,
        ?int $physicalRecordLength,
        callable $callable
    ): void {
        $record = new GroupRecord(physicalRecordLength: $physicalRecordLength);

        foreach ($input as $line) {
            $record->parseLine($line);
        }

        $callable($record);
    }

    // -- test field getters ---------------------------------------------------

    /**
     * @dataProvider inputLinesProducer
     */
    public function testGetUltimateReceiverIdentification(
        array $inputLines
    ): void {
        $this->withRecord($inputLines, null, function ($groupRecord) {
            $this->assertEquals(
                'abc',
                $groupRecord->getUltimateReceiverIdentification()
            );
        });
    }

    public function testGetUltimateReceiverIdentificationDefaulted(): void
    {
        $fileRecord = new GroupRecord(physicalRecordLength: null);
        $fileRecord->parseLine('02,,def,1,212209,0944,USD,2/');

        $this->assertNull($fileRecord->getUltimateReceiverIdentification());
    }

    /**
     * @dataProvider inputLinesProducer
     */
    public function testGetOriginatorIdentification(array $inputLines): void
    {
        $this->withRecord($inputLines, null, function ($groupRecord) {
            $this->assertEquals(
                'def',
                $groupRecord->getOriginatorIdentification()
            );
        });
    }

    public function testGetOriginatorIdentificationMissing(): void
    {
        $fileRecord = new GroupRecord(physicalRecordLength: null);
        $fileRecord->parseLine('02,abc,,1,212209,0944,USD,2/');

        $this->expectException(MalformedInputException::class);
        $this->expectExceptionMessage('Encountered issue trying to parse Group Header Field. Invalid field type: ');
        $fileRecord->getOriginatorIdentification();
    }

    /**
     * @dataProvider inputLinesProducer
     */
    public function testGetGroupStatus(array $inputLines): void
    {
        $this->withRecord($inputLines, null, function ($groupRecord) {
            $this->assertEquals('1', $groupRecord->getGroupStatus());
        });
    }

    public function testGetGroupStatusMissing(): void
    {
        $fileRecord = new GroupRecord(physicalRecordLength: null);
        $fileRecord->parseLine('02,abc,def,,212209,0944,USD,2/');

        $this->expectException(MalformedInputException::class);
        $this->expectExceptionMessage('Encountered issue trying to parse Group Header Field. Invalid field type: ');
        $fileRecord->getGroupStatus();
    }

    /**
     * @dataProvider inputLinesProducer
     */
    public function testGetAsOfDate(array $inputLines): void
    {
        $this->withRecord($inputLines, null, function ($groupRecord) {
            $this->assertEquals('212209', $groupRecord->getAsOfDate());
        });
    }

    public function testGetAsOfDateMissing(): void
    {
        $fileRecord = new GroupRecord(physicalRecordLength: null);
        $fileRecord->parseLine('02,abc,def,1,,0944,USD,2/');

        $this->expectException(MalformedInputException::class);
        $this->expectExceptionMessage('Encountered issue trying to parse Group Header Field. Invalid field type: ');
        $fileRecord->getAsOfDate();
    }

    /**
     * @dataProvider inputLinesProducer
     */
    public function testGetAsOfTime(array $inputLines): void
    {
        $this->withRecord($inputLines, null, function ($groupRecord) {
            $this->assertEquals('0944', $groupRecord->getAsOfTime());
        });
    }

    public function testGetAsOfTimeDefaulted(): void
    {
        $fileRecord = new GroupRecord(physicalRecordLength: null);
        $fileRecord->parseLine('02,abc,def,1,212209,,USD,2/');

        $this->assertNull($fileRecord->getAsOfTime());
    }

    /**
     * @dataProvider inputLinesProducer
     */
    public function testGetCurrencyCode(array $inputLines): void
    {
        $this->withRecord($inputLines, null, function ($groupRecord) {
            $this->assertEquals('USD', $groupRecord->getCurrencyCode());
        });
    }

    public function testGetCurrencyCodeDefaulted(): void
    {
        $fileRecord = new GroupRecord(physicalRecordLength: null);
        $fileRecord->parseLine('02,abc,def,1,212209,0944,,2/');

        $this->assertNull($fileRecord->getCurrencyCode());
    }

    /**
     * @dataProvider inputLinesProducer
     */
    public function testGetAsOfDateModifier(array $inputLines): void
    {
        $this->withRecord($inputLines, null, function ($groupRecord) {
            $this->assertEquals('2', $groupRecord->getAsOfDateModifier());
        });
    }

    public function testGetAsOfDateModifierDefaulted(): void
    {
        $fileRecord = new GroupRecord(physicalRecordLength: null);
        $fileRecord->parseLine('02,abc,def,1,212209,0944,USD,/');

        $this->assertNull($fileRecord->getAsOfDateModifier());
    }

    /**
     * @dataProvider inputLinesProducer
     */
    public function testGetGroupControlTotal(array $inputLines): void
    {
        $this->withRecord($inputLines, null, function ($groupRecord) {
            $this->assertEquals('10000', $groupRecord->getGroupControlTotal());
        });
    }

    public function testGetGroupControlTotalMissing(): void
    {
        $fileRecord = new GroupRecord(physicalRecordLength: null);
        $fileRecord->parseLine('02,abc,def,1,212209,0944,USD,2/');
        $fileRecord->parseLine('98,,1,6/');

        $this->expectException(MalformedInputException::class);
        $this->expectExceptionMessage('Encountered issue trying to parse Group Trailer Field. Invalid field type: ');
        $fileRecord->getGroupControlTotal();
    }

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
