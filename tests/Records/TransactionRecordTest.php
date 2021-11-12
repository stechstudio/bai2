<?php

declare(strict_types=1);

namespace STS\Bai2\Records;

use PHPUnit\Framework\TestCase;

use STS\Bai2\Exceptions\MalformedInputException;

final class TransactionRecordTest extends TestCase
{

    public function gettersProducer(): array
    {
        return [
            ['getTypeCode'],
            ['getAmount'],
            ['getFundsType'],
            ['getBankReferenceNumber'],
            ['getCustomerReferenceNumber'],
            ['getText'],
        ];
    }

    public function inputLinesProducer(): array
    {
        return [
            [[
                '16,409,10000,D,3,1,1000,5,10000,30,25000,1337,0042,WELCOME TO THE NEVERHOOD'
            ]],
            [[
                '16,409,10000/',
                '88,D,3/',
                '88,1,1000/',
                '88,5,10000/',
                '88,30,25000/',
                '88,1337,0042/',
                '88,WELCOME TO THE NEVERHOOD'
            ]],
        ];
    }

    public function inputLinesTooLongProducer(): array
    {
        return [
            [[
                '16,409,10000,D,3,1,1000,5,10000,30,25000,1337,0042,WELCOME TO THE NEVERHOOD, WHERE KLAY ROAMS FREE'
            ]],
            [[
                '16,409,10000/--------------------------------------------------------------------',
                '88,D,3/',
                '88,1,1000/',
                '88,5,10000/',
                '88,30,25000/',
                '88,1337,0042/',
                '88,WELCOME TO THE NEVERHOOD'
            ]],
            [[
                '16,409,10000/',
                '88,D,3/',
                '88,1,1000/',
                '88,5,10000/',
                '88,30,25000/---------------------------------------------------------------------',
                '88,1337,0042/',
                '88,WELCOME TO THE NEVERHOOD'
            ]],
        ];
    }

    protected function withRecord(
        array $input,
        ?int $physicalRecordLength,
        callable $callable
    ): void {
        $record = new TransactionRecord(physicalRecordLength: $physicalRecordLength);

        foreach ($input as $line) {
            $record->parseLine($line);
        }

        $callable($record);
    }

    // -- test field getters ---------------------------------------------------

    /**
     * @dataProvider inputLinesProducer
     */
    public function testGetTypeCode(array $inputLines): void
    {
        $this->withRecord($inputLines, null, function ($txnRecord) {
            $this->assertEquals('409', $txnRecord->getTypeCode());
        });
    }

    public function testGetTypeCodeMissing(): void
    {
        $txnRecord = new TransactionRecord(physicalRecordLength: null);
        $txnRecord->parseLine('16,,10000,D,3,1,1000,5,10000,30,25000,1337,0042,WELCOME TO THE NEVERHOOD');

        $this->expectException(MalformedInputException::class);
        $this->expectExceptionMessage('Encountered issue trying to parse Transaction Field. Invalid field type: ');
        $txnRecord->getTypeCode();
    }

    /**
     * @dataProvider inputLinesProducer
     */
    public function testGetAmount(array $inputLines): void
    {
        $this->withRecord($inputLines, null, function ($txnRecord) {
            $this->assertEquals('10000', $txnRecord->getAmount());
        });
    }

    public function testGetAmountDefaulted(): void
    {
        $txnRecord = new TransactionRecord(physicalRecordLength: null);
        $txnRecord->parseLine('16,409,,D,3,1,1000,5,10000,30,25000,1337,0042,WELCOME TO THE NEVERHOOD');

        $this->assertNull($txnRecord->getAmount());
    }

    /**
     * @dataProvider inputLinesProducer
     */
    public function testGetFundsType(array $inputLines): void
    {
        $this->withRecord($inputLines, null, function ($txnRecord) {
            $this->assertEquals(
                [
                    'distributionOfAvailability' => 'D',
                    'availability' => [
                         1 =>  1000,
                         5 => 10000,
                        30 => 25000,
                    ]
                ],
                $txnRecord->getFundsType()
            );
        });
    }

    public function testGetFundsTypeDefaulted(): void
    {
        $txnRecord = new TransactionRecord(physicalRecordLength: null);
        $txnRecord->parseLine('16,409,10000,,1337,0042,WELCOME TO THE NEVERHOOD');

        $this->assertEquals(
            ['distributionOfAvailability' => null],
            $txnRecord->getFundsType()
        );
    }

    /**
     * @dataProvider inputLinesProducer
     */
    public function testGetBankReferenceNumber(array $inputLines): void
    {
        $this->withRecord($inputLines, null, function ($txnRecord) {
            $this->assertEquals('1337', $txnRecord->getBankReferenceNumber());
        });
    }

    public function testGetBankReferenceNumberDefaulted(): void
    {
        $txnRecord = new TransactionRecord(physicalRecordLength: null);
        $txnRecord->parseLine('16,409,10000,D,3,1,1000,5,10000,30,25000,,0042,WELCOME TO THE NEVERHOOD');

        $this->assertNull($txnRecord->getBankReferenceNumber());
    }

    /**
     * @dataProvider inputLinesProducer
     */
    public function testGetCustomerReferenceNumber(array $inputLines): void
    {
        $this->withRecord($inputLines, null, function ($txnRecord) {
            $this->assertEquals(
                '0042',
                $txnRecord->getCustomerReferenceNumber()
            );
        });
    }

    public function testGetCustomerReferenceNumberDefaulted(): void
    {
        $txnRecord = new TransactionRecord(physicalRecordLength: null);
        $txnRecord->parseLine('16,409,10000,D,3,1,1000,5,10000,30,25000,1337,,WELCOME TO THE NEVERHOOD');

        $this->assertNull($txnRecord->getCustomerReferenceNumber());
    }

    /**
     * @dataProvider inputLinesProducer
     */
    public function testGetText(array $inputLines): void
    {
        $this->withRecord($inputLines, null, function ($txnRecord) {
            $this->assertEquals(
                'WELCOME TO THE NEVERHOOD',
                $txnRecord->getText()
            );
        });
    }

    public function testGetTextDefaulted(): void
    {
        $txnRecord = new TransactionRecord(physicalRecordLength: null);
        $txnRecord->parseLine('16,409,10000,D,3,1,1000,5,10000,30,25000,1337,0042,/');

        $this->assertNull($txnRecord->getText());
    }

    // -- test overall functionality -------------------------------------------

    // TODO(zmd): test ::toArray()

    // -- test overall error handling ------------------------------------------

    /**
     * @dataProvider inputLinesTooLongProducer
     */
    public function testPhysicalRecordLengthEnforced(array $inputLines): void
    {
        $this->expectException(MalformedInputException::class);
        $this->expectExceptionMessage('Input line length exceeds requested physical record length.');
        $this->withRecord($inputLines, 80, function ($txnRecord) {});
    }

    /**
     * @dataProvider gettersProducer
     */
    public function testFieldAccessWhenRecordNeverProcessed(
        string $getterMethod
    ): void {
        $txnRecord = new TransactionRecord(physicalRecordLength: null);

        $this->expectException(MalformedInputException::class);
        $this->expectExceptionMessage('Cannot access a Transaction field prior to reading an incoming Transaction line.');
        $txnRecord->$getterMethod();
    }

    public function testTryingToParseContinuationOutOfTurn(): void
    {
        $txnRecord = new TransactionRecord(physicalRecordLength: null);

        $this->expectException(MalformedInputException::class);
        $this->expectExceptionMessage('Cannot process a continuation without first processing something that can be continued.');
        $txnRecord->parseLine('88,D,3/');
    }

    // TODO(zmd): public function testTryingToProcessUnknownRecordType(): void {}

    /**
     * @dataProvider gettersProducer
     */
    public function testTryingToProcessIncompleteRecord(
        string $getterMethod
    ): void {
        $txnRecord = new TransactionRecord(physicalRecordLength: null);
        $txnRecord->parseLine('16,409,10000/');

        $this->expectException(MalformedInputException::class);
        $this->expectExceptionMessage('Cannot access a Transaction field from an incomplete or malformed Transaction line.');
        $txnRecord->$getterMethod();
    }

    // TODO(zmd): public function testTryingToProcessMalformedRecord(): void {}

}
