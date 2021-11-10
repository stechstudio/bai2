<?php

namespace STS\Bai2\Records;

use PHPUnit\Framework\TestCase;

use STS\Bai2\Exceptions\MalformedInputException;

final class FileRecordTest extends TestCase
{

    public function headerGettersProducer(): array
    {
        return [[
            'getSenderIdentification',
            'getReceiverIdentification',
            'getFileCreationDate',
            'getFileCreationTime',
            'getFileIdentificationNumber',
            'getPhysicalRecordLength',
            'getBlockSize',
            'getVersionNumber',
        ]];
    }

    public function trailerGettersProducer(): array
    {
        return [[
            'getFileControlTotal',
            'getNumberOfGroups',
            'getNumberOfRecords',
        ]];
    }

    public function inputLinesProducer(): array
    {
        return [
            [[
                '01,SENDR1,RECVR1,210616,1700,01,80,10,2/',
                '02,abc,def,1,212209,,,/',
                '98,10000,0,1/',
                '02,uvw,xyz,1,212209,,,/',
                '98,5000,0,1/',
                '99,1337,2,42/',
            ]],
            [[
                '01,SENDR1,RECVR1/',
                '88,210616,1700,01,80,10,2/',
                '02,abc,def/',
                '88,1,212209,,,/',
                '98,10000/',
                '88,0,1/',
                '02,uvw/',
                '88,xyz,1,212209,,,/',
                '98,5000,0,1/',
                '99,1337/',
                '88,2,42/',
            ]],
        ];
    }

    public function inputLinesTooLongProducer(): array
    {
        return [
            [[
                '01,SENDR1,RECVR1,210616,1700,01,80,10,2/-----------------------------------------',
                '02,abc,def,1,212209,,,/',
                '98,10000,0,1/',
                '02,uvw,xyz,1,212209,,,/',
                '98,5000,0,1/',
                '99,1337,2,42/',
            ]],
            [[
                '01,SENDR1,RECVR1,210616,1700,01,80,10,2/',
                '02,abc,def,1,212209,,,/',
                '98,10000,0,1/',
                '02,uvw,xyz,1,212209,,,/',
                '98,5000,0,1/',
                '99,1337,2,42/--------------------------------------------------------------------',
            ]],
            [[
                '01,SENDR1,RECVR1/',
                '88,210616,1700,01,80,10,2/-------------------------------------------------------',
                '02,abc,def,1,212209,,,/',
                '98,10000,0,1/',
                '02,uvw,xyz,1,212209,,,/',
                '98,5000,0,1/',
                '99,1337,2,42/',
            ]],
            [[
                '01,SENDR1,RECVR1,210616,1700,01,80,10,2/',
                '02,abc,def,1,212209,,,/',
                '98,10000,0,1/',
                '02,uvw,xyz,1,212209,,,/',
                '98,5000,0,1/',
                '99,1337,2/',
                '88,42/---------------------------------------------------------------------------',
            ]],
            [[
                '01,SENDR1,RECVR1,210616,1700,01,80,10,2/',
                '02,abc,def,1,212209,,,/',
                '98,10000,0,1/',
                '02,uvw,xyz,1,212209,,,/----------------------------------------------------------',
                '98,5000,0,1/',
                '99,1337,2,42/',
            ]],
            [[
                '01,SENDR1,RECVR1,210616,1700,01,80,10,2/',
                '02,abc,def,1,212209,,,/',
                '98,10000,0,1/',
                '02,uvw,xyz,1,212209,,,/',
                '98,5000,0,1/---------------------------------------------------------------------',
                '99,1337,2,42/',
            ]],
        ];
    }

    protected function withRecord(array $input, callable $callable): void
    {
        $record = new FileRecord();

        foreach ($input as $line) {
            $record->parseLine($line);
        }

        $callable($record);
    }

    // -- test field getters ---------------------------------------------------

    /**
     * @dataProvider inputLinesProducer
     */
    public function testGetSenderIdentification(array $inputLines): void
    {
        $this->withRecord($inputLines, function ($fileRecord) {
            $this->assertEquals('SENDR1', $fileRecord->getSenderIdentification());
        });
    }

    public function testGetSenderIdentifiationMissing(): void
    {
        $fileRecord = new FileRecord();
        $fileRecord->parseLine('01,,RECVR1,210616,1700,01,80,10,2/');

        $this->expectException(MalformedInputException::class);
        $this->expectExceptionMessage('Encountered issue trying to parse File Header Field. Invalid field type: ');
        $fileRecord->getSenderIdentification();
    }

    /**
     * @dataProvider inputLinesProducer
     */
    public function testGetReceiverIdentification(array $inputLines): void
    {
        $this->withRecord($inputLines, function ($fileRecord) {
            $this->assertEquals('RECVR1', $fileRecord->getReceiverIdentification());
        });
    }

    public function testGetReceiverIdentifiationMissing(): void
    {
        $fileRecord = new FileRecord();
        $fileRecord->parseLine('01,SENDR1,,210616,1700,01,80,10,2/');

        $this->expectException(MalformedInputException::class);
        $this->expectExceptionMessage('Encountered issue trying to parse File Header Field. Invalid field type: ');
        $fileRecord->getReceiverIdentification();
    }

    /**
     * @dataProvider inputLinesProducer
     */
    public function testGetFileCreationDate(array $inputLines): void
    {
        $this->withRecord($inputLines, function ($fileRecord) {
            $this->assertEquals('210616', $fileRecord->getFileCreationDate());
        });
    }

    public function testGetFileCreationDateMissing(): void
    {
        $fileRecord = new FileRecord();
        $fileRecord->parseLine('01,SENDR1,RECVR1,,1700,01,80,10,2/');

        $this->expectException(MalformedInputException::class);
        $this->expectExceptionMessage('Encountered issue trying to parse File Header Field. Invalid field type: ');
        $fileRecord->getFileCreationDate();
    }

    /**
     * @dataProvider inputLinesProducer
     */
    public function testGetFileCreationTime(array $inputLines): void
    {
        $this->withRecord($inputLines, function ($fileRecord) {
            $this->assertEquals('1700', $fileRecord->getFileCreationTime());
        });
    }

    // TODO(zmd): public function testGetFileCreationTimeMissing(): void {}

    /**
     * @dataProvider inputLinesProducer
     */
    public function testGetFileIdentificationNumber(array $inputLines): void
    {
        $this->withRecord($inputLines, function ($fileRecord) {
            $this->assertEquals('01', $fileRecord->getFileIdentificationNumber());
        });
    }

    // TODO(zmd): public function testGetFileIdentificationNumberMissing(): void {}

    /**
     * @dataProvider inputLinesProducer
     */
    public function testGetPhysicalRecordLength(array $inputLines): void
    {
        $this->withRecord($inputLines, function ($fileRecord) {
            $this->assertEquals(80, $fileRecord->getPhysicalRecordLength());
        });
    }

    // TODO(zmd): public function testGetPhysicalRecordLengthDefaulted(): void {}

    /**
     * @dataProvider inputLinesProducer
     */
    public function testGetBlockSize(array $inputLines): void
    {
        $this->withRecord($inputLines, function ($fileRecord) {
            $this->assertEquals(10, $fileRecord->getBlockSize());
        });
    }

    // TODO(zmd): public function testGetBlockSizeDefaulted(): void {}

    /**
     * @dataProvider inputLinesProducer
     */
    public function testGetVersionNumber(array $inputLines): void
    {
        $this->withRecord($inputLines, function ($fileRecord) {
            $this->assertEquals('2', $fileRecord->getVersionNumber());
        });
    }

    // TODO(zmd): public function testGetVersionNumberMissing(): void {}

    /**
     * @dataProvider inputLinesProducer
     */
    public function testGetFileControlTotal(array $inputLines): void
    {
        $this->withRecord($inputLines, function ($fileRecord) {
            $this->assertEquals(1337, $fileRecord->getFileControlTotal());
        });
    }

    // TODO(zmd): public function testGetFileControlTotalMissing(): void {}

    /**
     * @dataProvider inputLinesProducer
     */
    public function testGetNumberOfGroups(array $inputLines): void
    {
        $this->withRecord($inputLines, function ($fileRecord) {
            $this->assertEquals(2, $fileRecord->getNumberOfGroups());
        });
    }

    // TODO(zmd): public function testGetNumberOfGroupsMissing(): void {}

    /**
     * @dataProvider inputLinesProducer
     */
    public function testGetNumberOfRecords(array $inputLines): void
    {
        $this->withRecord($inputLines, function ($fileRecord) {
            $this->assertEquals(42, $fileRecord->getNumberOfRecords());
        });
    }

    // TODO(zmd): public function testGetNumberOfRecordsMissing(): void {}

    /**
     * @dataProvider inputLinesProducer
     */
    public function testGetGroups(array $inputLines): void
    {
        $this->withRecord($inputLines, function ($fileRecord) {
            $this->assertEquals(2, count($fileRecord->getGroups()));
        });
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
        $this->withRecord($inputLines, function ($fileRecord) {});
    }

    /**
     * @dataProvider headerGettersProducer
     */
    public function testHeaderFieldAccessWhenHeaderNeverProcessed(
        string $headerGetterMethod
    ): void {
        $fileRecord = new FileRecord();

        $this->expectException(MalformedInputException::class);
        $this->expectExceptionMessage('Cannot access a File Header field prior to reading an incoming File Header line.');
        $fileRecord->$headerGetterMethod();
    }

    /**
     * @dataProvider trailerGettersProducer
     */
    public function testTrailerFieldAccessWhenTrailerNeverProcessed(
        string $trailerGetterMethod
    ): void {
        $fileRecord = new FileRecord();

        $this->expectException(MalformedInputException::class);
        $this->expectExceptionMessage('Cannot access a File Trailer field prior to reading an incoming File Trailer line.');
        $fileRecord->$trailerGetterMethod();
    }

    public function testTryingToParseContinuationOutOfTurn(): void
    {
        $fileRecord = new FileRecord();

        $this->expectException(MalformedInputException::class);
        $this->expectExceptionMessage('Cannot process a continuation without first processing something that can be continued.');
        $fileRecord->parseLine('88,210616,1700,01,80,10,2/');
    }

    public function testTryingToProcessChildLineBeforeChildInitialized(): void
    {
        $fileRecord = new FileRecord();
        $fileRecord->parseLine('01,SENDR1,RECVR1,210616,1700,01,80,10,2/');

        $this->expectException(MalformedInputException::class);
        $this->expectExceptionMessage('Cannot process Group Trailer, Account-related, or Transaction-related line before processing the Group Header line.');
        $fileRecord->parseLine('98,10000,0,1/');
    }

    /**
     * @dataProvider headerGettersProducer
     */
    public function testTryingToProcessIncompleteHeader(
        string $headerGetterMethod
    ): void {
        $fileRecord = new FileRecord();
        $fileRecord->parseLine('01,SENDR1,RECVR1/');

        $this->expectException(MalformedInputException::class);
        $this->expectExceptionMessage('Cannot access a File Header field from an incomplete or malformed File Header line.');
        $fileRecord->$headerGetterMethod();
    }

    /**
     * @dataProvider trailerGettersProducer
     */
    public function testTryingToProcessIncompleteTrailer(
        string $trailerGetterMethod
    ): void {
        $fileRecord = new FileRecord();
        $fileRecord->parseLine('01,SENDR1,RECVR1,210616,1700,01,,10,2/');
        $fileRecord->parseLine('99,1337/');

        $this->expectException(MalformedInputException::class);
        $this->expectExceptionMessage('Cannot access a File Trailer field from an incomplete or malformed File Trailer line.');
        $fileRecord->$trailerGetterMethod();
    }

    /**
     * @dataProvider headerGettersProducer
     */
    public function testTryingToProcessMalformedHeader(
        string $headerGetterMethod
    ): void {
        $fileRecord = new FileRecord();
        $fileRecord->parseLine('01,SENDR1,RECVR1,210616,1700,01,80,10,2');

        $this->expectException(MalformedInputException::class);
        $this->expectExceptionMessage('Cannot access a File Header field from an incomplete or malformed File Header line.');
        $fileRecord->$headerGetterMethod();
    }

    /**
     * @dataProvider trailerGettersProducer
     */
    public function testTryingToProcessMalformedTrailer(
        string $trailerGetterMethod
    ): void {
        $fileRecord = new FileRecord();
        $fileRecord->parseLine('01,SENDR1,RECVR1,210616,1700,01,,10,2/');
        $fileRecord->parseLine('99,1337,2,42');

        $this->expectException(MalformedInputException::class);
        $this->expectExceptionMessage('Cannot access a File Trailer field from an incomplete or malformed File Trailer line.');
        $fileRecord->$trailerGetterMethod();
    }

}
