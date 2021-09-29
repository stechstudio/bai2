<?php

namespace STS\Bai2\Records;

use STS\Bai2\Bai2;

class FileRecord extends AbstractEnvelopeRecord
{

    protected string $recordCode = '01';

    protected string $senderIdentification;

    protected string $receiverIdentification;

    protected string $fileCreationDate;

    protected string $fileCreationTime;

    protected string $fileIdentificationNumber;

    protected ?int $physicalRecordLength = null;

    protected ?int $blockSize = null;

    protected string $versionNumber = '2';

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

    public function getRecordCode(): string
    {
        return $this->recordCode;
    }

    public function getSenderIdentification(): string
    {
        return $this->senderIdentification;
    }

    public function getReceiverIdentification(): string
    {
        return $this->receiverIdentification;
    }

    public function getFileCreationDate(): string
    {
        return $this->fileCreationDate;
    }

    public function getFileCreationTime(): string
    {
        return $this->fileCreationTime;
    }

    public function getFileIdentificationNumber(): string
    {
        return $this->fileIdentificationNumber;
    }

    public function getPhysicalRecordLength(): ?int
    {
        return $this->physicalRecordLength;
    }

    public function getBlockSize(): ?int
    {
        return $this->blockSize;
    }

    public function getVersionNumber(): string
    {
        return $this->versionNumber;
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
            $recordCode,
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

        $this->recordCode = $recordCode;
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
        // TODO(zmd): parse? hahaha, yah right!
        $this->records[] = $line;
        $this->setFinalized(true);
    }

    private function normalizeEmptyString(string $s): ?string
    {
        return $s === '' ? null : $s;
    }

}
