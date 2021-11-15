<?php

declare(strict_types=1);

namespace STS\Bai2\Records;

use PHPUnit\Framework\TestCase;

use STS\Bai2\Exceptions\MalformedInputException;

final class FileRecordTest extends TestCase
{

    public function headerGettersProducer(): array
    {
        return [
            ['getSenderIdentification'],
            ['getReceiverIdentification'],
            ['getFileCreationDate'],
            ['getFileCreationTime'],
            ['getFileIdentificationNumber'],
            ['getPhysicalRecordLength'],
            ['getBlockSize'],
            ['getVersionNumber'],
        ];
    }

    public function trailerGettersProducer(): array
    {
        return [
            ['getFileControlTotal'],
            ['getNumberOfGroups'],
            ['getNumberOfRecords'],
        ];
    }

    public function inputLinesProducer(): array
    {
        return [
            [[
                '01,SENDR1,RECVR1,210616,1700,01,80,10,2/',
                '02,abc,def,1,212209,0944,USD,2/',
                '03,0001,USD,010,500000,,,190,70000000,4,0/',
                '16,409,10000,D,3,1,1000,5,10000,30,25000,1337,0042,WELCOME TO THE NEVERHOOD',
                '16,409,10000,D,3,1,1000,5,10000,30,25000,1337,0043,EARTHWORM JIM LAUNCHES COW',
                '49,70520000,4/',
                '03,0002,USD,010,500000,,,190,70000000,4,0/',
                '16,409,10000,D,3,1,1000,5,10000,30,25000,1337,0042,WELCOME TO THE NEVERHOOD',
                '16,409,10000,D,3,1,1000,5,10000,30,25000,1337,0043,EARTHWORM JIM LAUNCHES COW',
                '49,70520000,4/',
                '98,10000,2,6/',
                '02,uvw,xyz,1,212209,0944,USD,2/',
                '03,0001,USD,010,500000,,,190,70000000,4,0/',
                '16,409,10000,D,3,1,1000,5,10000,30,25000,1337,0042,WELCOME TO THE NEVERHOOD',
                '16,409,10000,D,3,1,1000,5,10000,30,25000,1337,0043,EARTHWORM JIM LAUNCHES COW',
                '49,70520000,4/',
                '03,0002,USD,010,500000,,,190,70000000,4,0/',
                '16,409,10000,D,3,1,1000,5,10000,30,25000,1337,0042,WELCOME TO THE NEVERHOOD',
                '16,409,10000,D,3,1,1000,5,10000,30,25000,1337,0043,EARTHWORM JIM LAUNCHES COW',
                '49,70520000,4/',
                '98,5000,0,1/',
                '99,1337,2,42/',
            ]],
            [[
                '01,SENDR1,RECVR1/',
                '88,210616,1700,01,80,10,2/',
                '02,abc,def/',
                '88,1,212209,0944,USD,2/',
                '03,0001,USD,010,500000/',
                '88,,,190,70000000,4,0/',
                '16,409,10000,D,3,1,1000,5,10000,30,25000/',
                '88,1337,0042,WELCOME TO THE NEVERHOOD',
                '16,409,10000,D,3,1,1000,5,10000,30,25000/',
                '88,1337,0043,EARTHWORM JIM LAUNCHES COW',
                '49,70520000,4/',
                '03,0002,USD,010,500000/',
                '88,,,190,70000000,4,0/',
                '16,409,10000,D,3,1,1000,5,10000,30,25000/',
                '88,1337,0042,WELCOME TO THE NEVERHOOD',
                '16,409,10000,D,3,1,1000,5,10000,30,25000/',
                '88,1337,0043,EARTHWORM JIM LAUNCHES COW',
                '49,70520000,4/',
                '88,2/',
                '98,10000,2/',
                '88,6/',
                '02,uvw,xyz/',
                '88,1,212209,0944,USD,2/',
                '03,0001,USD,010,500000/',
                '88,,,190,70000000,4,0/',
                '16,409,10000,D,3,1,1000,5,10000,30,25000/',
                '88,1337,0042,WELCOME TO THE NEVERHOOD',
                '16,409,10000,D,3,1,1000,5,10000,30,25000/',
                '88,1337,0043,EARTHWORM JIM LAUNCHES COW',
                '49,70520000,4/',
                '03,0002,USD,010,500000/',
                '88,,,190,70000000,4,0/',
                '16,409,10000,D,3,1,1000,5,10000,30,25000/',
                '88,1337,0042,WELCOME TO THE NEVERHOOD',
                '16,409,10000,D,3,1,1000,5,10000,30,25000/',
                '88,1337,0043,EARTHWORM JIM LAUNCHES COW',
                '49,70520000,4/',
                '88,2/',
                '98,5000,0/',
                '88,1/',
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
                '02,abc,def,1,212209,0944,USD,2/',
                '03,0001,USD,010,500000,,,190,70000000,4,0/',
                '16,409,10000,D,3,1,1000,5,10000,30,25000,1337,0042,WELCOME TO THE NEVERHOOD',
                '16,409,10000,D,3,1,1000,5,10000,30,25000,1337,0043,EARTHWORM JIM LAUNCHES COW',
                '49,70520000,4/',
                '03,0002,USD,010,500000,,,190,70000000,4,0/',
                '16,409,10000,D,3,1,1000,5,10000,30,25000,1337,0042,WELCOME TO THE NEVERHOOD',
                '16,409,10000,D,3,1,1000,5,10000,30,25000,1337,0043,EARTHWORM JIM LAUNCHES COW',
                '49,70520000,4/',
                '98,10000,2,6/',
                '02,uvw,xyz,1,212209,0944,USD,2/',
                '03,0001,USD,010,500000,,,190,70000000,4,0/',
                '16,409,10000,D,3,1,1000,5,10000,30,25000,1337,0042,WELCOME TO THE NEVERHOOD',
                '16,409,10000,D,3,1,1000,5,10000,30,25000,1337,0043,EARTHWORM JIM LAUNCHES COW',
                '49,70520000,4/',
                '03,0002,USD,010,500000,,,190,70000000,4,0/',
                '16,409,10000,D,3,1,1000,5,10000,30,25000,1337,0042,WELCOME TO THE NEVERHOOD',
                '16,409,10000,D,3,1,1000,5,10000,30,25000,1337,0043,EARTHWORM JIM LAUNCHES COW',
                '49,70520000,4/',
                '98,5000,0,1/',
                '99,1337,2,42/',
            ]],
            [[
                '01,SENDR1,RECVR1,210616,1700,01,80,10,2/',
                '02,abc,def,1,212209,0944,USD,2/',
                '03,0001,USD,010,500000,,,190,70000000,4,0/',
                '16,409,10000,D,3,1,1000,5,10000,30,25000,1337,0042,WELCOME TO THE NEVERHOOD',
                '16,409,10000,D,3,1,1000,5,10000,30,25000,1337,0043,EARTHWORM JIM LAUNCHES COW',
                '49,70520000,4/',
                '03,0002,USD,010,500000,,,190,70000000,4,0/',
                '16,409,10000,D,3,1,1000,5,10000,30,25000,1337,0042,WELCOME TO THE NEVERHOOD',
                '16,409,10000,D,3,1,1000,5,10000,30,25000,1337,0043,EARTHWORM JIM LAUNCHES COW',
                '49,70520000,4/',
                '98,10000,2,6/',
                '02,uvw,xyz,1,212209,0944,USD,2/',
                '03,0001,USD,010,500000,,,190,70000000,4,0/',
                '16,409,10000,D,3,1,1000,5,10000,30,25000,1337,0042,WELCOME TO THE NEVERHOOD',
                '16,409,10000,D,3,1,1000,5,10000,30,25000,1337,0043,EARTHWORM JIM LAUNCHES COW',
                '49,70520000,4/',
                '03,0002,USD,010,500000,,,190,70000000,4,0/',
                '16,409,10000,D,3,1,1000,5,10000,30,25000,1337,0042,WELCOME TO THE NEVERHOOD',
                '16,409,10000,D,3,1,1000,5,10000,30,25000,1337,0043,EARTHWORM JIM LAUNCHES COW',
                '49,70520000,4/',
                '98,5000,0,1/',
                '99,1337,2,42/--------------------------------------------------------------------',
            ]],
            [[
                '01,SENDR1,RECVR1/',
                '88,210616,1700,01,80,10,2/-------------------------------------------------------',
                '02,abc,def,1,212209,0944,USD,2/',
                '03,0001,USD,010,500000,,,190,70000000,4,0/',
                '16,409,10000,D,3,1,1000,5,10000,30,25000,1337,0042,WELCOME TO THE NEVERHOOD',
                '16,409,10000,D,3,1,1000,5,10000,30,25000,1337,0043,EARTHWORM JIM LAUNCHES COW',
                '49,70520000,4/',
                '03,0002,USD,010,500000,,,190,70000000,4,0/',
                '16,409,10000,D,3,1,1000,5,10000,30,25000,1337,0042,WELCOME TO THE NEVERHOOD',
                '16,409,10000,D,3,1,1000,5,10000,30,25000,1337,0043,EARTHWORM JIM LAUNCHES COW',
                '49,70520000,4/',
                '98,10000,2,6/',
                '02,uvw,xyz,1,212209,0944,USD,2/',
                '03,0001,USD,010,500000,,,190,70000000,4,0/',
                '16,409,10000,D,3,1,1000,5,10000,30,25000,1337,0042,WELCOME TO THE NEVERHOOD',
                '16,409,10000,D,3,1,1000,5,10000,30,25000,1337,0043,EARTHWORM JIM LAUNCHES COW',
                '49,70520000,4/',
                '03,0002,USD,010,500000,,,190,70000000,4,0/',
                '16,409,10000,D,3,1,1000,5,10000,30,25000,1337,0042,WELCOME TO THE NEVERHOOD',
                '16,409,10000,D,3,1,1000,5,10000,30,25000,1337,0043,EARTHWORM JIM LAUNCHES COW',
                '49,70520000,4/',
                '98,5000,0,1/',
                '99,1337,2,42/',
            ]],
            [[
                '01,SENDR1,RECVR1,210616,1700,01,80,10,2/',
                '02,abc,def,1,212209,0944,USD,2/',
                '03,0001,USD,010,500000,,,190,70000000,4,0/',
                '16,409,10000,D,3,1,1000,5,10000,30,25000,1337,0042,WELCOME TO THE NEVERHOOD',
                '16,409,10000,D,3,1,1000,5,10000,30,25000,1337,0043,EARTHWORM JIM LAUNCHES COW',
                '49,70520000,4/',
                '03,0002,USD,010,500000,,,190,70000000,4,0/',
                '16,409,10000,D,3,1,1000,5,10000,30,25000,1337,0042,WELCOME TO THE NEVERHOOD',
                '16,409,10000,D,3,1,1000,5,10000,30,25000,1337,0043,EARTHWORM JIM LAUNCHES COW',
                '49,70520000,4/',
                '98,10000,2,6/',
                '02,uvw,xyz,1,212209,0944,USD,2/',
                '03,0001,USD,010,500000,,,190,70000000,4,0/',
                '16,409,10000,D,3,1,1000,5,10000,30,25000,1337,0042,WELCOME TO THE NEVERHOOD',
                '16,409,10000,D,3,1,1000,5,10000,30,25000,1337,0043,EARTHWORM JIM LAUNCHES COW',
                '49,70520000,4/',
                '03,0002,USD,010,500000,,,190,70000000,4,0/',
                '16,409,10000,D,3,1,1000,5,10000,30,25000,1337,0042,WELCOME TO THE NEVERHOOD',
                '16,409,10000,D,3,1,1000,5,10000,30,25000,1337,0043,EARTHWORM JIM LAUNCHES COW',
                '49,70520000,4/',
                '98,5000,0,1/',
                '99,1337,2/',
                '88,42/---------------------------------------------------------------------------',
            ]],
            [[
                '01,SENDR1,RECVR1,210616,1700,01,80,10,2/',
                '02,abc,def,1,212209,0944,USD,2/',
                '03,0001,USD,010,500000,,,190,70000000,4,0/',
                '16,409,10000,D,3,1,1000,5,10000,30,25000,1337,0042,WELCOME TO THE NEVERHOOD',
                '16,409,10000,D,3,1,1000,5,10000,30,25000,1337,0043,EARTHWORM JIM LAUNCHES COW',
                '49,70520000,4/',
                '03,0002,USD,010,500000,,,190,70000000,4,0/',
                '16,409,10000,D,3,1,1000,5,10000,30,25000,1337,0042,WELCOME TO THE NEVERHOOD',
                '16,409,10000,D,3,1,1000,5,10000,30,25000,1337,0043,EARTHWORM JIM LAUNCHES COW',
                '49,70520000,4/',
                '98,10000,2,6/',
                '02,uvw,xyz,1,212209,0944,USD,2/--------------------------------------------------',
                '03,0001,USD,010,500000,,,190,70000000,4,0/',
                '16,409,10000,D,3,1,1000,5,10000,30,25000,1337,0042,WELCOME TO THE NEVERHOOD',
                '16,409,10000,D,3,1,1000,5,10000,30,25000,1337,0043,EARTHWORM JIM LAUNCHES COW',
                '49,70520000,4/',
                '03,0002,USD,010,500000,,,190,70000000,4,0/',
                '16,409,10000,D,3,1,1000,5,10000,30,25000,1337,0042,WELCOME TO THE NEVERHOOD',
                '16,409,10000,D,3,1,1000,5,10000,30,25000,1337,0043,EARTHWORM JIM LAUNCHES COW',
                '49,70520000,4/',
                '98,5000,0,1/',
                '99,1337,2,42/',
            ]],
            [[
                '01,SENDR1,RECVR1,210616,1700,01,80,10,2/',
                '02,abc,def,1,212209,0944,USD,2/',
                '03,0001,USD,010,500000,,,190,70000000,4,0/',
                '16,409,10000,D,3,1,1000,5,10000,30,25000,1337,0042,WELCOME TO THE NEVERHOOD',
                '16,409,10000,D,3,1,1000,5,10000,30,25000,1337,0043,EARTHWORM JIM LAUNCHES COW',
                '49,70520000,4/',
                '03,0002,USD,010,500000,,,190,70000000,4,0/',
                '16,409,10000,D,3,1,1000,5,10000,30,25000,1337,0042,WELCOME TO THE NEVERHOOD',
                '16,409,10000,D,3,1,1000,5,10000,30,25000,1337,0043,EARTHWORM JIM LAUNCHES COW',
                '49,70520000,4/',
                '98,10000,2,6/',
                '02,uvw,xyz,1,212209,0944,USD,2/',
                '03,0001,USD,010,500000,,,190,70000000,4,0/',
                '16,409,10000,D,3,1,1000,5,10000,30,25000,1337,0042,WELCOME TO THE NEVERHOOD',
                '16,409,10000,D,3,1,1000,5,10000,30,25000,1337,0043,EARTHWORM JIM LAUNCHES COW',
                '49,70520000,4/',
                '03,0002,USD,010,500000,,,190,70000000,4,0/',
                '16,409,10000,D,3,1,1000,5,10000,30,25000,1337,0042,WELCOME TO THE NEVERHOOD',
                '16,409,10000,D,3,1,1000,5,10000,30,25000,1337,0043,EARTHWORM JIM LAUNCHES COW',
                '49,70520000,4/',
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

    public function testGetFileCreationTimeMissing(): void
    {
        $fileRecord = new FileRecord();
        $fileRecord->parseLine('01,SENDR1,RECVR1,210616,,01,80,10,2/');

        $this->expectException(MalformedInputException::class);
        $this->expectExceptionMessage('Encountered issue trying to parse File Header Field. Invalid field type: ');
        $fileRecord->getFileCreationTime();
    }

    /**
     * @dataProvider inputLinesProducer
     */
    public function testGetFileIdentificationNumber(array $inputLines): void
    {
        $this->withRecord($inputLines, function ($fileRecord) {
            $this->assertEquals('01', $fileRecord->getFileIdentificationNumber());
        });
    }

    public function testGetFileIdentificationNumberMissing(): void
    {
        $fileRecord = new FileRecord();
        $fileRecord->parseLine('01,SENDR1,RECVR1,210616,1700,,80,10,2/');

        $this->expectException(MalformedInputException::class);
        $this->expectExceptionMessage('Encountered issue trying to parse File Header Field. Invalid field type: ');
        $fileRecord->getFileIdentificationNumber();
    }

    /**
     * @dataProvider inputLinesProducer
     */
    public function testGetPhysicalRecordLength(array $inputLines): void
    {
        $this->withRecord($inputLines, function ($fileRecord) {
            $this->assertEquals(80, $fileRecord->getPhysicalRecordLength());
        });
    }

    public function testGetPhysicalRecordLengthDefaulted(): void
    {
        $fileRecord = new FileRecord();
        $fileRecord->parseLine('01,SENDR1,RECVR1,210616,1700,01,,10,2/');

        $this->assertNull($fileRecord->getPhysicalRecordLength());
    }

    /**
     * @dataProvider inputLinesProducer
     */
    public function testGetBlockSize(array $inputLines): void
    {
        $this->withRecord($inputLines, function ($fileRecord) {
            $this->assertEquals(10, $fileRecord->getBlockSize());
        });
    }

    public function testGetBlockSizeDefaulted(): void
    {
        $fileRecord = new FileRecord();
        $fileRecord->parseLine('01,SENDR1,RECVR1,210616,1700,01,80,,2/');

        $this->assertNull($fileRecord->getBlockSize());
    }

    /**
     * @dataProvider inputLinesProducer
     */
    public function testGetVersionNumber(array $inputLines): void
    {
        $this->withRecord($inputLines, function ($fileRecord) {
            $this->assertEquals('2', $fileRecord->getVersionNumber());
        });
    }

    public function testGetVersionNumberMissing(): void
    {
        $fileRecord = new FileRecord();
        $fileRecord->parseLine('01,SENDR1,RECVR1,210616,1700,01,80,10,/');

        $this->expectException(MalformedInputException::class);
        $this->expectExceptionMessage('Encountered issue trying to parse File Header Field. Invalid field type: ');
        $fileRecord->getVersionNumber();
    }

    /**
     * @dataProvider inputLinesProducer
     */
    public function testGetFileControlTotal(array $inputLines): void
    {
        $this->withRecord($inputLines, function ($fileRecord) {
            $this->assertEquals(1337, $fileRecord->getFileControlTotal());
        });
    }

    public function testGetFileControlTotalMissing(): void
    {
        $fileRecord = new FileRecord();
        $fileRecord->parseLine('01,SENDR1,RECVR1,210616,1700,01,80,10,2/');
        $fileRecord->parseLine('99,,2,42/');

        $this->expectException(MalformedInputException::class);
        $this->expectExceptionMessage('Encountered issue trying to parse File Trailer Field. Invalid field type: ');
        $fileRecord->getFileControlTotal();
    }

    /**
     * @dataProvider inputLinesProducer
     */
    public function testGetNumberOfGroups(array $inputLines): void
    {
        $this->withRecord($inputLines, function ($fileRecord) {
            $this->assertEquals(2, $fileRecord->getNumberOfGroups());
        });
    }

    public function testGetNumberOfGroupsMissing(): void
    {
        $fileRecord = new FileRecord();
        $fileRecord->parseLine('01,SENDR1,RECVR1,210616,1700,01,80,10,2/');
        $fileRecord->parseLine('99,1337,,42/');

        $this->expectException(MalformedInputException::class);
        $this->expectExceptionMessage('Encountered issue trying to parse File Trailer Field. Invalid field type: ');
        $fileRecord->getNumberOfGroups();
    }

    /**
     * @dataProvider inputLinesProducer
     */
    public function testGetNumberOfRecords(array $inputLines): void
    {
        $this->withRecord($inputLines, function ($fileRecord) {
            $this->assertEquals(42, $fileRecord->getNumberOfRecords());
        });
    }

    public function testGetNumberOfRecordsMissing(): void
    {
        $fileRecord = new FileRecord();
        $fileRecord->parseLine('01,SENDR1,RECVR1,210616,1700,01,80,10,2/');
        $fileRecord->parseLine('99,1337,2,/');

        $this->expectException(MalformedInputException::class);
        $this->expectExceptionMessage('Encountered issue trying to parse File Trailer Field. Invalid field type: ');
        $fileRecord->getNumberOfRecords();
    }

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

    /**
     * @dataProvider inputLinesProducer
     */
    public function testToArray(array $inputLines): void
    {
        $this->withRecord($inputLines, function ($fileRecord) {
            $this->assertEquals(
                [
                    'senderIdentification' => 'SENDR1',
                    'receiverIdentification' => 'RECVR1',
                    'fileCreationDate' => '210616',
                    'fileCreationTime' => '1700',
                    'fileIdentificationNumber' => '01',
                    'physicalRecordLength' => 80,
                    'blockSize' => 10,
                    'versionNumber' => '2',
                    'fileControlTotal' => 1337,
                    'numberOfGroups' => 2,
                    'numberOfRecords' => 42,
                    'groups' => [
                        [
                            'ultimateReceiverIdentification' => 'abc',
                            'originatorIdentification' => 'def',
                            'groupStatus' => '1',
                            'asOfDate' => '212209',
                            'asOfTime' => '0944',
                            'currencyCode' => 'USD',
                            'asOfDateModifier' => '2',
                            'groupControlTotal' => 10000,
                            'numberOfAccounts' => 2,
                            'numberOfRecords' => 6,
                            'accounts' => [
                                [
                                    'customerAccountNumber' => '0001',
                                    'currencyCode' => 'USD',
                                    'summaryAndStatusInformation' => [
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
                                    'accountControlTotal' => 70520000,
                                    'numberOfRecords' => 4,
                                    'transactions' => [
                                        [
                                            'typeCode' => '409',
                                            'amount' => 10000,
                                            'fundsType' => [
                                                'distributionOfAvailability' => 'D',
                                                'availability' => [
                                                     1 =>  1000,
                                                     5 => 10000,
                                                    30 => 25000,
                                                ]
                                            ],
                                            'bankReferenceNumber' => '1337',
                                            'customerReferenceNumber' => '0042',
                                            'text' => 'WELCOME TO THE NEVERHOOD',
                                        ],
                                        [
                                            'typeCode' => '409',
                                            'amount' => 10000,
                                            'fundsType' => [
                                                'distributionOfAvailability' => 'D',
                                                'availability' => [
                                                     1 =>  1000,
                                                     5 => 10000,
                                                    30 => 25000,
                                                ]
                                            ],
                                            'bankReferenceNumber' => '1337',
                                            'customerReferenceNumber' => '0043',
                                            'text' => 'EARTHWORM JIM LAUNCHES COW',
                                        ],
                                    ],
                                ],
                                [
                                    'customerAccountNumber' => '0002',
                                    'currencyCode' => 'USD',
                                    'summaryAndStatusInformation' => [
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
                                    'accountControlTotal' => 70520000,
                                    'numberOfRecords' => 4,
                                    'transactions' => [
                                        [
                                            'typeCode' => '409',
                                            'amount' => 10000,
                                            'fundsType' => [
                                                'distributionOfAvailability' => 'D',
                                                'availability' => [
                                                     1 =>  1000,
                                                     5 => 10000,
                                                    30 => 25000,
                                                ]
                                            ],
                                            'bankReferenceNumber' => '1337',
                                            'customerReferenceNumber' => '0042',
                                            'text' => 'WELCOME TO THE NEVERHOOD',
                                        ],
                                        [
                                            'typeCode' => '409',
                                            'amount' => 10000,
                                            'fundsType' => [
                                                'distributionOfAvailability' => 'D',
                                                'availability' => [
                                                     1 =>  1000,
                                                     5 => 10000,
                                                    30 => 25000,
                                                ]
                                            ],
                                            'bankReferenceNumber' => '1337',
                                            'customerReferenceNumber' => '0043',
                                            'text' => 'EARTHWORM JIM LAUNCHES COW',
                                        ],
                                    ],
                                ],
                            ]
                        ],
                        [
                            'ultimateReceiverIdentification' => 'uvw',
                            'originatorIdentification' => 'xyz',
                            'groupStatus' => '1',
                            'asOfDate' => '212209',
                            'asOfTime' => '0944',
                            'currencyCode' => 'USD',
                            'asOfDateModifier' => '2',
                            'groupControlTotal' => 5000,
                            'numberOfAccounts' => 0,
                            'numberOfRecords' => 1,
                            'accounts' => [
                                [
                                    'customerAccountNumber' => '0001',
                                    'currencyCode' => 'USD',
                                    'summaryAndStatusInformation' => [
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
                                    'accountControlTotal' => 70520000,
                                    'numberOfRecords' => 4,
                                    'transactions' => [
                                        [
                                            'typeCode' => '409',
                                            'amount' => 10000,
                                            'fundsType' => [
                                                'distributionOfAvailability' => 'D',
                                                'availability' => [
                                                     1 =>  1000,
                                                     5 => 10000,
                                                    30 => 25000,
                                                ]
                                            ],
                                            'bankReferenceNumber' => '1337',
                                            'customerReferenceNumber' => '0042',
                                            'text' => 'WELCOME TO THE NEVERHOOD',
                                        ],
                                        [
                                            'typeCode' => '409',
                                            'amount' => 10000,
                                            'fundsType' => [
                                                'distributionOfAvailability' => 'D',
                                                'availability' => [
                                                     1 =>  1000,
                                                     5 => 10000,
                                                    30 => 25000,
                                                ]
                                            ],
                                            'bankReferenceNumber' => '1337',
                                            'customerReferenceNumber' => '0043',
                                            'text' => 'EARTHWORM JIM LAUNCHES COW',
                                        ],
                                    ],
                                ],
                                [
                                    'customerAccountNumber' => '0002',
                                    'currencyCode' => 'USD',
                                    'summaryAndStatusInformation' => [
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
                                    'accountControlTotal' => 70520000,
                                    'numberOfRecords' => 4,
                                    'transactions' => [
                                        [
                                            'typeCode' => '409',
                                            'amount' => 10000,
                                            'fundsType' => [
                                                'distributionOfAvailability' => 'D',
                                                'availability' => [
                                                     1 =>  1000,
                                                     5 => 10000,
                                                    30 => 25000,
                                                ]
                                            ],
                                            'bankReferenceNumber' => '1337',
                                            'customerReferenceNumber' => '0042',
                                            'text' => 'WELCOME TO THE NEVERHOOD',
                                        ],
                                        [
                                            'typeCode' => '409',
                                            'amount' => 10000,
                                            'fundsType' => [
                                                'distributionOfAvailability' => 'D',
                                                'availability' => [
                                                     1 =>  1000,
                                                     5 => 10000,
                                                    30 => 25000,
                                                ]
                                            ],
                                            'bankReferenceNumber' => '1337',
                                            'customerReferenceNumber' => '0043',
                                            'text' => 'EARTHWORM JIM LAUNCHES COW',
                                        ],
                                    ],
                                ],
                            ]
                        ],
                    ]
                ],
                $fileRecord->toArray()
            );
        });
    }

    // TODO(zmd): public function testToArrayWhenFieldDefaulted(): void {}

    // TODO(zmd): public function testToArrayWhenHeaderFieldInvalid(): void {}

    // TODO(zmd): public function testToArrayWhenTrailerFieldInvalid(): void {}

    // TODO(zmd): public function testToArrayWhenRequiredHeaderFieldMissing(): void {}

    // TODO(zmd): public function testToArrayWhenRequiredTrailerFieldMissing(): void {}

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
