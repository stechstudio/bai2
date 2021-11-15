<?php

declare(strict_types=1);

namespace STS\Bai2\Records;

use PHPUnit\Framework\TestCase;

use STS\Bai2\Exceptions\MalformedInputException;

final class AccountRecordTest extends TestCase
{

    public function headerGettersProducer(): array
    {
        return [
            ['getCustomerAccountNumber'],
            ['getCurrencyCode'],
            ['getSummaryAndStatusInformation'],
        ];
    }

    public function trailerGettersProducer(): array
    {
        return [
            ['getAccountControlTotal'],
            ['getNumberOfRecords'],
        ];
    }

    public function inputLinesProducer(): array
    {
        return [
            [[
                '03,0001,USD,010,500000,,,190,70000000,4,0/',
                '16,409,10000,0,123456789,987654321,SOME TEXT',
                '16,409,10000,2,123456789,987654321,SOME TEXT',
                '49,70520000,4/',
            ]],
            [[
                '03,0001,USD/',
                '88,010,500000,,/',
                '88,190,70000000,4,0/',
                '16,409,10000,0/',
                '88,123456789,987654321,SOME TEXT',
                '16,409,10000,2,123456789/',
                '88,987654321,SOME TEXT',
                '49,70520000/',
                '88,4/',
            ]],
        ];
    }

    public function inputLinesTooLongProducer(): array
    {
        return [
            [[
                '03,0001,USD,010,500000,,,190,70000000,4,0/---------------------------------------',
                '16,409,10000,0,123456789,987654321,SOME TEXT',
                '16,409,10000,2,123456789,987654321,SOME TEXT',
                '49,70520000,4/',
            ]],
            [[
                '03,0001,USD,010,500000,,,190,70000000,4,0/',
                '16,409,10000,0,123456789,987654321,SOME TEXT',
                '16,409,10000,2,123456789,987654321,SOME TEXT',
                '49,70520000,4/-------------------------------------------------------------------',
            ]],
            [[
                '03,0001,USD/',
                '88,010,500000,,/-----------------------------------------------------------------',
                '88,190,70000000,4,0/',
                '16,409,10000,0/',
                '88,123456789,987654321,SOME TEXT',
                '16,409,10000,2,123456789/',
                '88,987654321,SOME TEXT',
                '49,70520000/',
                '88,4/',
            ]],
            [[
                '03,0001,USD/',
                '88,010,500000,,/',
                '88,190,70000000,4,0/',
                '16,409,10000,0/',
                '88,123456789,987654321,SOME TEXT',
                '16,409,10000,2,123456789/',
                '88,987654321,SOME TEXT',
                '49,70520000/',
                '88,4/----------------------------------------------------------------------------',
            ]],
            [[
                '03,0001,USD,010,500000,,,190,70000000,4,0/',
                '16,409,10000,0,123456789,987654321,SOMETIMES I CALL THEM POTATO BUGS, SOMETIMES I CALL THEM PILLBUGS, AND OFTENTIMES I CALL THEM SOWBUGS, BUT NEVER EVER DO I CALL THEM ROLY-POLYS',
                '16,409,10000,2,123456789,987654321,SOME TEXT',
                '49,70520000,4/',
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

    /**
     * @dataProvider inputLinesProducer
     */
    public function testGetTransactions(array $inputLines): void
    {
        $this->withRecord($inputLines, null, function ($accountRecord) {
            $this->assertEquals(2, count($accountRecord->getTransactions()));
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
        $this->withRecord($inputLines, 80, function ($accountRecord) {});
    }

    /**
     * @dataProvider headerGettersProducer
     */
    public function testHeaderFieldAccessWhenHeaderNeverProcessed(
        string $headerGetterMethod
    ): void {
        $accountRecord = new AccountRecord(physicalRecordLength: null);

        $this->expectException(MalformedInputException::class);
        $this->expectExceptionMessage('Cannot access a Account Identifier and Summary Status field prior to reading an incoming Account Identifier and Summary Status line.');
        $accountRecord->$headerGetterMethod();
    }

    /**
     * @dataProvider trailerGettersProducer
     */
    public function testTrailerFieldAccessWhenTrailerNeverProcessed(
        string $trailerGetterMethod
    ): void {
        $accountRecord = new AccountRecord(physicalRecordLength: null);

        $this->expectException(MalformedInputException::class);
        $this->expectExceptionMessage('Cannot access a Account Trailer field prior to reading an incoming Account Trailer line.');
        $accountRecord->$trailerGetterMethod();
    }

    public function testTryingToParseContinuationOutOfTurn(): void
    {
        $accountRecord = new AccountRecord(physicalRecordLength: null);

        $this->expectException(MalformedInputException::class);
        $this->expectExceptionMessage('Cannot process a continuation without first processing something that can be continued.');
        $accountRecord->parseLine('88,010,500000,,/');
    }

    public function testTryingToProcessChildLineBeforeChildInitialized(): void
    {
        $accountRecord = new AccountRecord(physicalRecordLength: null);

        $this->expectException(MalformedInputException::class);
        $this->expectExceptionMessage('Cannot process Transaction-related line before processing the main Transaction line.');
        $accountRecord->parseLine('17,haha,as if there is a 17 record type,but how would AccountRecord know? :P');
    }

    /**
     * @dataProvider headerGettersProducer
     */
    public function testTryingToProcessIncompleteHeader(
        string $headerGetterMethod
    ): void {
        $accountRecord = new AccountRecord(physicalRecordLength: null);
        $accountRecord->parseLine('03,0001,USD/');

        $this->expectException(MalformedInputException::class);
        $this->expectExceptionMessage('Cannot access a Account Identifier and Summary Status field from an incomplete or malformed Account Identifier and Summary Status line.');
        $accountRecord->$headerGetterMethod();
    }

    /**
     * @dataProvider trailerGettersProducer
     */
    public function testTryingToProcessIncompleteTrailer(
        string $trailerGetterMethod
    ): void {
        $accountRecord = new AccountRecord(physicalRecordLength: null);
        $accountRecord->parseLine('49,70520000/');

        $this->expectException(MalformedInputException::class);
        $this->expectExceptionMessage('Cannot access a Account Trailer field from an incomplete or malformed Account Trailer line.');
        $accountRecord->$trailerGetterMethod();
    }

    /**
     * @dataProvider headerGettersProducer
     */
    public function testTryingToProcessMalformedHeader(
        string $headerGetterMethod
    ): void {
        $accountRecord = new AccountRecord(physicalRecordLength: null);
        $accountRecord->parseLine('03,0001,USD,010,500000,,,190,70000000,4,0');

        $this->expectException(MalformedInputException::class);
        $this->expectExceptionMessage('Cannot access a Account Identifier and Summary Status field from an incomplete or malformed Account Identifier and Summary Status line.');
        $accountRecord->$headerGetterMethod();
    }

    /**
     * @dataProvider trailerGettersProducer
     */
    public function testTryingToProcessMalformedTrailer(
        string $trailerGetterMethod
    ): void {
        $accountRecord = new AccountRecord(physicalRecordLength: null);
        $accountRecord->parseLine('49,70520000,4');

        $this->expectException(MalformedInputException::class);
        $this->expectExceptionMessage('Cannot access a Account Trailer field from an incomplete or malformed Account Trailer line.');
        $accountRecord->$trailerGetterMethod();
    }

}
