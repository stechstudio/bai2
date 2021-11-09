<?php

namespace STS\Bai2\Records;

use STS\Bai2\Bai2;

use STS\Bai2\Parsers\FileHeaderParser;
use STS\Bai2\Parsers\FileTrailerParser;

class FileRecord
{

    protected string $currentRecordCode;

    protected FileHeaderParser $headerParser;

    protected FileTrailerParser $trailerParser;

    protected array $groups = [];

    protected GroupRecord $currentChild;

    public function parseLine(string $line): void
    {
        match ($recordCode = Bai2::recordTypeCode($line)) {
            '01' => $this->processFileHeader($recordCode, $line),
            '88' => $this->processContinuation($line),
            '99' => $this->processFileTrailer($recordCode, $line),
            default => $this->processChildRecord($recordCode, $line)
        };
    }

    public function getSenderIdentification(): string
    {
        return $this->headerParser['senderIdentification'];
    }

    public function getReceiverIdentification(): string
    {
        return $this->headerParser['receiverIdentification'];
    }

    public function getFileCreationDate(): string
    {
        return $this->headerParser['fileCreationDate'];
    }

    public function getFileCreationTime(): string
    {
        return $this->headerParser['fileCreationTime'];
    }

    public function getFileIdentificationNumber(): string
    {
        return $this->headerParser['fileIdentificationNumber'];
    }

    public function getPhysicalRecordLength(): ?int
    {
        return $this->headerParser['physicalRecordLength'];
    }

    public function getBlockSize(): ?int
    {
        return $this->headerParser['blockSize'];
    }

    public function getVersionNumber(): string
    {
        return $this->headerParser['versionNumber'];
    }

    public function getFileControlTotal(): int
    {
        return $this->trailerParser['fileControlTotal'];
    }

    public function getNumberOfGroups(): int
    {
        return $this->trailerParser['numberOfGroups'];
    }

    public function getNumberOfRecords(): int
    {
        return $this->trailerParser['numberOfRecords'];
    }

    public function getGroups(): array
    {
        return $this->groups;
    }

    // -------------------------------------------------------------------------

    protected function processFileHeader(string $recordCode, string $line): void
    {
        $this->currentRecordCode = $recordCode;
        $this->headerParser = new FileHeaderParser();
        $this->headerParser->pushLine($line);
    }

    protected function processFileTrailer(string $recordCode, string $line): void
    {
        $this->currentRecordCode = $recordCode;
        $this->trailerParser = new FileTrailerParser();
        $this->trailerParser->pushLine($line);
    }

    protected function processContinuation(string $line): void
    {
        match ($this->currentRecordCode) {
            '01' => $this->headerParser->pushLine($line),
            '99' => $this->trailerParser->pushLine($line),
            default => $this->currentChild->parseLine($line)
        };
    }

    protected function processChildRecord(string $recordCode, string $line): void
    {
        $this->currentRecordCode = $recordCode;

        switch ($recordCode) {

            case '02':
                // TODO(zmd): propagate fileRecordLength, if appropriate
                $this->currentChild = new GroupRecord();
                $this->groups[] = $this->currentChild;
                $this->currentChild->parseLine($line);
                break;

            default:
                $this->currentChild->parseLine($line);
                break;

        }
    }

}
