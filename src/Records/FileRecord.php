<?php

namespace STS\Bai2\Records;

use STS\Bai2\Bai2;

use STS\Bai2\Parsers\MultilineParser;

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

    protected ?MultilineParser $headerParser = null;

    protected ?MultilineParser $trailerParser = null;

    public function parseLine(string $line): void
    {
        switch (Bai2::recordTypeCode($line)) {
            case '01':
                $this->pushHeaderLine($line);
                break;
            case '88':
                $this->parseOrDelegateContinuation($line);
                break;
            case '99':
                $this->pushTrailerLine($line);
                break;
            default:
                $this->delegateToChild($line);
                break;
        }
    }

    protected function pushHeaderLine(string $line): void
    {
        if (!$this->headerParser) {
            $this->headerParser = new MultilineParser($line);
        } else {
            $this->headerParser->continue($line);
        }
    }

    protected function pushTrailerLine(string $line): void
    {
        if (!$this->trailerParser) {
            $this->trailerParser = new MultilineParser($line);
        } else {
            $this->trailerParser->continue($line);
        }
    }

    public function getSenderIdentification(): string
    {
        return $this->senderIdentification ??
            $this->parseHeader()->senderIdentification;
    }

    public function getReceiverIdentification(): string
    {
        return $this->receiverIdentification ??
            $this->parseHeader()->receiverIdentification ;
    }

    public function getFileCreationDate(): string
    {
        return $this->fileCreationDate ??
            $this->parseHeader()->fileCreationDate ;
    }

    public function getFileCreationTime(): string
    {
        return $this->fileCreationTime ??
            $this->parseHeader()->fileCreationTime ;
    }

    public function getFileIdentificationNumber(): string
    {
        return $this->fileIdentificationNumber ??
            $this->parseHeader()->fileIdentificationNumber ;
    }

    public function getPhysicalRecordLength(): ?int
    {
        return $this->physicalRecordLength ??
            $this->parseHeader()->physicalRecordLength ;
    }

    public function getBlockSize(): ?int
    {
        return $this->blockSize ??
            $this->parseHeader()->blockSize ;
    }

    public function getVersionNumber(): string
    {
        return $this->versionNumber ??
            $this->parseHeader()->versionNumber ;
    }

    public function getFileControlTotal(): int
    {
        return $this->fileControlTotal ??
            $this->parseTrailer()->fileControlTotal;
    }

    public function getNumberOfGroups(): int
    {
        return $this->numberOfGroups ??
            $this->parseTrailer()->numberOfGroups;
    }

    public function getNumberOfRecords(): int
    {
        return $this->numberOfRecords ??
            $this->parseTrailer()->numberOfRecords;
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

    protected function parseHeader(): self
    {
        // TODO(zmd): error handling if $headerParser was never initialized?
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
        ] = $this->headerParser->drop(9);

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

        return $this;
    }

    protected function parseContinuation(string $line): void
    {
        // TODO(zmd): parse? hahaha, yah right!
        $this->records[] = $line;
    }

    protected function parseTrailer(): void
    {
        // TODO(zmd): error handling if $trailerParser was never initialized?
        [
            $_recordCode,
            $fileControlTotal,
            $numberOfGroups,
            $numberOfRecords
        ] = $this->trailerParser->drop(4);
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
