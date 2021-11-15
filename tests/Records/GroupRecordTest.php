<?php

declare(strict_types=1);

namespace STS\Bai2\Records;

use PHPUnit\Framework\TestCase;

use STS\Bai2\Exceptions\MalformedInputException;

final class GroupRecordTest extends TestCase
{

    public function headerGettersProducer(): array
    {
        return [
            ['getUltimateReceiverIdentification'],
            ['getOriginatorIdentification'],
            ['getGroupStatus'],
            ['getAsOfDate'],
            ['getAsOfTime'],
            ['getCurrencyCode'],
            ['getAsOfDateModifier'],
        ];
    }

    public function trailerGettersProducer(): array
    {
        return [
            ['getGroupControlTotal'],
            ['getNumberOfAccounts'],
            ['getNumberOfRecords'],
        ];
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
                '98,10000,2,6/',
            ]],
            [[
                '02,abc,def/',
                '88,1,212209,0944,USD,2/',
                '03,0001,,/',
                '88,,,/',
                '49,0,2/',
                '03,0002,,,,/',
                '88,/',
                '49,0/',
                '88,2/',
                '98,10000,2/',
                '88,6/',
            ]],
        ];
    }

    public function inputLinesTooLongProducer(): array
    {
        return [
            [[
                '02,abc,def,1,212209,0944,USD,2/--------------------------------------------------',
                '03,0001,,,,,/',
                '49,0,2/',
                '03,0002,,,,,/',
                '49,0,2/',
                '98,10000,2,6/',
            ]],
            [[
                '02,abc,def,1,212209,0944,USD,2/',
                '03,0001,,,,,/',
                '49,0,2/',
                '03,0002,,,,,/',
                '49,0,2/',
                '98,10000,2,6/--------------------------------------------------------------------',
            ]],
            [[
                '02,abc,def/',
                '88,1,212209,0944,USD,2/----------------------------------------------------------',
                '03,0001,,/',
                '88,,,/',
                '49,0,2/',
                '03,0002,,,,/',
                '88,/',
                '49,0/',
                '88,2/',
                '98,10000,2/',
                '88,6/',
            ]],
            [[
                '02,abc,def/',
                '88,1,212209,0944,USD,2/',
                '03,0001,,/',
                '88,,,/',
                '49,0,2/',
                '03,0002,,,,/',
                '88,/',
                '49,0/',
                '88,2/',
                '98,10000,2/',
                '88,6/----------------------------------------------------------------------------',
            ]],
            [[
                '02,abc,def,1,212209,0944,USD,2/',
                '03,0001,,,,,/--------------------------------------------------------------------',
                '49,0,2/',
                '03,0002,,,,,/',
                '49,0,2/',
                '98,10000,2,6/',
            ]],
            [[
                '02,abc,def,1,212209,0944,USD,2/',
                '03,0001,,,,,/',
                '49,0,2/--------------------------------------------------------------------------',
                '03,0002,,,,,/',
                '49,0,2/',
                '98,10000,2,6/',
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
        $groupRecord = new GroupRecord(physicalRecordLength: null);
        $groupRecord->parseLine('02,,def,1,212209,0944,USD,2/');

        $this->assertNull($groupRecord->getUltimateReceiverIdentification());
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
        $groupRecord = new GroupRecord(physicalRecordLength: null);
        $groupRecord->parseLine('02,abc,,1,212209,0944,USD,2/');

        $this->expectException(MalformedInputException::class);
        $this->expectExceptionMessage('Encountered issue trying to parse Group Header Field. Invalid field type: ');
        $groupRecord->getOriginatorIdentification();
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
        $groupRecord = new GroupRecord(physicalRecordLength: null);
        $groupRecord->parseLine('02,abc,def,,212209,0944,USD,2/');

        $this->expectException(MalformedInputException::class);
        $this->expectExceptionMessage('Encountered issue trying to parse Group Header Field. Invalid field type: ');
        $groupRecord->getGroupStatus();
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
        $groupRecord = new GroupRecord(physicalRecordLength: null);
        $groupRecord->parseLine('02,abc,def,1,,0944,USD,2/');

        $this->expectException(MalformedInputException::class);
        $this->expectExceptionMessage('Encountered issue trying to parse Group Header Field. Invalid field type: ');
        $groupRecord->getAsOfDate();
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
        $groupRecord = new GroupRecord(physicalRecordLength: null);
        $groupRecord->parseLine('02,abc,def,1,212209,,USD,2/');

        $this->assertNull($groupRecord->getAsOfTime());
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
        $groupRecord = new GroupRecord(physicalRecordLength: null);
        $groupRecord->parseLine('02,abc,def,1,212209,0944,,2/');

        $this->assertNull($groupRecord->getCurrencyCode());
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
        $groupRecord = new GroupRecord(physicalRecordLength: null);
        $groupRecord->parseLine('02,abc,def,1,212209,0944,USD,/');

        $this->assertNull($groupRecord->getAsOfDateModifier());
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
        $groupRecord = new GroupRecord(physicalRecordLength: null);
        $groupRecord->parseLine('02,abc,def,1,212209,0944,USD,2/');
        $groupRecord->parseLine('98,,2,6/');

        $this->expectException(MalformedInputException::class);
        $this->expectExceptionMessage('Encountered issue trying to parse Group Trailer Field. Invalid field type: ');
        $groupRecord->getGroupControlTotal();
    }

    /**
     * @dataProvider inputLinesProducer
     */
    public function testGetNumberOfAccounts(array $inputLines): void
    {
        $this->withRecord($inputLines, null, function ($groupRecord) {
            $this->assertEquals('2', $groupRecord->getNumberOfAccounts());
        });
    }

    public function testGetNumberOfAccountsMissing(): void
    {
        $groupRecord = new GroupRecord(physicalRecordLength: null);
        $groupRecord->parseLine('02,abc,def,1,212209,0944,USD,2/');
        $groupRecord->parseLine('98,10000,,6/');

        $this->expectException(MalformedInputException::class);
        $this->expectExceptionMessage('Encountered issue trying to parse Group Trailer Field. Invalid field type: ');
        $groupRecord->getNumberOfAccounts();
    }

    /**
     * @dataProvider inputLinesProducer
     */
    public function testGetNumberOfRecords(array $inputLines): void
    {
        $this->withRecord($inputLines, null, function ($groupRecord) {
            $this->assertEquals('6', $groupRecord->getNumberOfRecords());
        });
    }

    public function testGetNumberOfRecordsMissing(): void
    {
        $groupRecord = new GroupRecord(physicalRecordLength: null);
        $groupRecord->parseLine('02,abc,def,1,212209,0944,USD,2/');
        $groupRecord->parseLine('98,10000,2,/');

        $this->expectException(MalformedInputException::class);
        $this->expectExceptionMessage('Encountered issue trying to parse Group Trailer Field. Invalid field type: ');
        $groupRecord->getNumberOfRecords();
    }

    /**
     * @dataProvider inputLinesProducer
     */
    public function testGetAccounts(array $inputLines): void
    {
        $this->withRecord($inputLines, null, function ($groupRecord) {
            $this->assertEquals(2, count($groupRecord->getAccounts()));
        });
    }

    // -- test overall functionality -------------------------------------------

    // TODO(zmd): public function testToArray(): void {}

    // TODO(zmd): public function testToArrayWhenFieldDefaulted(): void {}

    // TODO(zmd): public function testToArrayWhenFieldInvalid(): void {}

    // TODO(zmd): public function testToArrayWhenRequiredFieldMissing(): void {}

    // TODO(zmd): public function testToArrayWhenHeaderNeverProcessed(): void {}

    // TODO(zmd): public function testToArrayWhenTrailerNeverProcessed(): void {}

    // TODO(zmd): public function testToArrayWhenHeaderIncomplete(): void {}

    // TODO(zmd): public function testToArrayWhenTrailerIncomplete(): void {}

    // TODO(zmd): public function testToArrayWhenHeaderMalformed(): void {}

    // TODO(zmd): public function testToArrayWhenTrailerMalformed(): void {}

    // -- test overall error handling ------------------------------------------

    /**
     * @dataProvider inputLinesTooLongProducer
     */
    public function testPhysicalRecordLengthEnforced(array $inputLines): void
    {
        $this->expectException(MalformedInputException::class);
        $this->expectExceptionMessage('Input line length exceeds requested physical record length.');
        $this->withRecord($inputLines, 80, function ($groupRecord) {});
    }

    /**
     * @dataProvider headerGettersProducer
     */
    public function testHeaderFieldAccessWhenHeaderNeverProcessed(
        string $headerGetterMethod
    ): void {
        $groupRecord = new GroupRecord(physicalRecordLength: null);

        $this->expectException(MalformedInputException::class);
        $this->expectExceptionMessage('Cannot access a Group Header field prior to reading an incoming Group Header line.');
        $groupRecord->$headerGetterMethod();
    }

    /**
     * @dataProvider trailerGettersProducer
     */
    public function testTrailerFieldAccessWhenTrailerNeverProcessed(
        string $trailerGetterMethod
    ): void {
        $groupRecord = new GroupRecord(physicalRecordLength: null);

        $this->expectException(MalformedInputException::class);
        $this->expectExceptionMessage('Cannot access a Group Trailer field prior to reading an incoming Group Trailer line.');
        $groupRecord->$trailerGetterMethod();
    }

    public function testTryingToParseContinuationOutOfTurn(): void
    {
        $groupRecord = new GroupRecord(physicalRecordLength: null);

        $this->expectException(MalformedInputException::class);
        $this->expectExceptionMessage('Cannot process a continuation without first processing something that can be continued.');
        $groupRecord->parseLine('88,1,212209,0944,USD,2/');
    }

    public function testTryingToProcessChildLineBeforeChildInitialized(): void
    {
        $groupRecord = new GroupRecord(physicalRecordLength: null);

        $this->expectException(MalformedInputException::class);
        $this->expectExceptionMessage('Cannot process Account Trailer or Transaction-related line before processing the Account Header line.');
        $groupRecord->parseLine('49,0,2/');
    }

    /**
     * @dataProvider headerGettersProducer
     */
    public function testTryingToProcessIncompleteHeader(
        string $headerGetterMethod
    ): void {
        $groupRecord = new GroupRecord(physicalRecordLength: null);
        $groupRecord->parseLine('02,abc,def,1,212209/');

        $this->expectException(MalformedInputException::class);
        $this->expectExceptionMessage('Cannot access a Group Header field from an incomplete or malformed Group Header line.');
        $groupRecord->$headerGetterMethod();
    }

    /**
     * @dataProvider trailerGettersProducer
     */
    public function testTryingToProcessIncompleteTrailer(
        string $trailerGetterMethod
    ): void {
        $groupRecord = new GroupRecord(physicalRecordLength: null);
        $groupRecord->parseLine('98,10000/');

        $this->expectException(MalformedInputException::class);
        $this->expectExceptionMessage('Cannot access a Group Trailer field from an incomplete or malformed Group Trailer line.');
        $groupRecord->$trailerGetterMethod();
    }

    /**
     * @dataProvider headerGettersProducer
     */
    public function testTryingToProcessMalformedHeader(
        string $headerGetterMethod
    ): void {
        $groupRecord = new GroupRecord(physicalRecordLength: null);
        $groupRecord->parseLine('02,abc,def,1,212209,0944,USD,2');

        $this->expectException(MalformedInputException::class);
        $this->expectExceptionMessage('Cannot access a Group Header field from an incomplete or malformed Group Header line.');
        $groupRecord->$headerGetterMethod();
    }

    /**
     * @dataProvider trailerGettersProducer
     */
    public function testTryingToProcessMalformedTrailer(
        string $trailerGetterMethod
    ): void {
        $groupRecord = new GroupRecord(physicalRecordLength: null);
        $groupRecord->parseLine('98,10000,2,6');

        $this->expectException(MalformedInputException::class);
        $this->expectExceptionMessage('Cannot access a Group Trailer field from an incomplete or malformed Group Trailer line.');
        $groupRecord->$trailerGetterMethod();
    }

}
