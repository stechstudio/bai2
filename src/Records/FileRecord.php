<?php

namespace STS\Bai2\Records;

use STS\Bai2\Bai2;

class FileRecord extends AbstractEnvelopeRecord
{

    protected string $senderIdentification;

    protected string $receiverIdentification;

    protected string $fileCreationDate;

    protected string $fileCreationTime;

    protected string $fileIdentificationNumber;

    protected ?int $physicalRecordLength = null;

    protected ?int $blockSize = null;

    protected string $versionNumber = '2';

    protected int $fileControlTotal;

    protected int $numberOfGroups;

    protected int $numberOfRecords;

    public function parseLine(string $line): void
    {
        switch (Bai2::recordTypeCode($line)) {
            case '01':
                $this->parseHeader($line);
                break;
            case '88':
                $this->parseOrDelegateContinuation($line);
                break;
            case '99':
                $this->parseTrailer($line);
                break;
            default:
                $this->delegateToChild($line);
                break;
        }
    }

    protected function parseAllLines(): self
    {
        return $this;
    }

    public function getSenderIdentification(): string
    {
        return $this->senderIdentification ??
            $this->parseAllLines()->senderIdentification;
    }

    public function getReceiverIdentification(): string
    {
        return $this->receiverIdentification ??
            $this->parseAllLines()->receiverIdentification ;
    }

    public function getFileCreationDate(): string
    {
        return $this->fileCreationDate ??
            $this->parseAllLines()->fileCreationDate ;
    }

    public function getFileCreationTime(): string
    {
        return $this->fileCreationTime ??
            $this->parseAllLines()->fileCreationTime ;
    }

    public function getFileIdentificationNumber(): string
    {
        return $this->fileIdentificationNumber ??
            $this->parseAllLines()->fileIdentificationNumber ;
    }

    public function getPhysicalRecordLength(): ?int
    {
        return $this->physicalRecordLength ??
            $this->parseAllLines()->physicalRecordLength ;
    }

    public function getBlockSize(): ?int
    {
        return $this->blockSize ??
            $this->parseAllLines()->blockSize ;
    }

    public function getVersionNumber(): string
    {
        return $this->versionNumber ??
            $this->parseAllLines()->versionNumber ;
    }

    public function getFileControlTotal(): int
    {
        return $this->fileControlTotal ??
            $this->parseAllLines()->fileControlTotal;
    }

    public function getNumberOfGroups(): int
    {
        return $this->numberOfGroups ??
            $this->parseAllLines()->numberOfGroups;
    }

    public function getNumberOfRecords(): int
    {
        return $this->numberOfRecords ??
            $this->parseAllLines()->numberOfRecords;
    }

    public function groups(): array
    {
        // TODO(zmd): implement me
        return [];
    }

    protected function newChild(): void
    {
        $this->currentChild = new GroupRecord();
        $this->records[] = $this->currentChild;
    }

    protected function parseHeader(string $line): void
    {
        [
            $_recordCode,
            $senderIdentification,
            $receiverIdentification,
            $fileCreationDate,
            $fileCreationTime,
            $fileIdentificationNumber,
            $physicalRecordLength,
            $blockSize,
            $versionNumber
        ] = explode(',', $line);

        $physicalRecordLength = $this->normalizeEmptyString($physicalRecordLength);
        $blockSize = $this->normalizeEmptyString($blockSize);
        $versionNumber = rtrim($versionNumber, '/');

        // TODO(zmd): clean this up, we're going to want to deal with
        //   serializing to array in a different way
        $this->records[] = $line;

        $this->senderIdentification = $senderIdentification;
        $this->receiverIdentification = $receiverIdentification;
        $this->fileCreationDate = $fileCreationDate;
        $this->fileCreationTime = $fileCreationTime;
        $this->fileIdentificationNumber = $fileIdentificationNumber;
        $this->physicalRecordLength = $physicalRecordLength;
        $this->blockSize = $blockSize;
        $this->versionNumber = $versionNumber;
    }

    protected function parseContinuation(string $line): void
    {
        // TODO(zmd): parse? hahaha, yah right!
        $this->records[] = $line;
    }

    protected function parseTrailer(string $line): void
    {
        [
            $_recordCode,
            $fileControlTotal,
            $numberOfGroups,
            $numberOfRecords
        ] = explode(',', $line);
        $numberOfRecords = rtrim($numberOfRecords, '/');

        // TODO(zmd): clean this up, we're going to want to deal with
        //   serializing to array in a different way
        $this->records[] = $line;

        $this->fileControlTotal = $fileControlTotal;
        $this->numberOfGroups = $numberOfGroups;
        $this->numberOfRecords = $numberOfRecords;

        $this->setFinalized(true);
    }

    private function normalizeEmptyString(string $s): ?string
    {
        return $s === '' ? null : $s;
    }

}
