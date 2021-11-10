<?php

namespace STS\Bai2\Records;

use STS\Bai2\Bai2;

use STS\Bai2\Parsers\FileHeaderParser;
use STS\Bai2\Parsers\FileTrailerParser;

use STS\Bai2\Exceptions\MalformedInputException;

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
            '01' => $this->processHeader($recordCode, $line),
            '88' => $this->processContinuation($line),
            '99' => $this->processTrailer($recordCode, $line),
            default => $this->processChildRecord($recordCode, $line)
        };
    }

    // -- getters --------------------------------------------------------------

    public function getSenderIdentification(): string
    {
        return $this->headerField('senderIdentification');
    }

    public function getReceiverIdentification(): string
    {
        return $this->headerField('receiverIdentification');
    }

    public function getFileCreationDate(): string
    {
        return $this->headerField('fileCreationDate');
    }

    public function getFileCreationTime(): string
    {
        return $this->headerField('fileCreationTime');
    }

    public function getFileIdentificationNumber(): string
    {
        return $this->headerField('fileIdentificationNumber');
    }

    public function getPhysicalRecordLength(): ?int
    {
        return $this->headerField('physicalRecordLength');
    }

    public function getBlockSize(): ?int
    {
        return $this->headerField('blockSize');
    }

    public function getVersionNumber(): string
    {
        return $this->headerField('versionNumber');
    }

    public function getFileControlTotal(): int
    {
        return $this->trailerField('fileControlTotal');
    }

    public function getNumberOfGroups(): int
    {
        return $this->trailerField('numberOfGroups');
    }

    public function getNumberOfRecords(): int
    {
        return $this->trailerField('numberOfRecords');
    }

    public function getGroups(): array
    {
        return $this->groups;
    }

    // -- helper methods -------------------------------------------------------

    protected function headerField(string $fieldKey): null|string|int
    {
        try {
            return $this->headerParser[$fieldKey];
        } catch (\Error) {
            throw new MalformedInputException('Cannot access a File Header field prior to reading an incoming File Header line.');
        }
    }

    protected function trailerField(string $fieldKey): null|string|int
    {
        try {
            return $this->trailerParser[$fieldKey];
        } catch (\Error) {
            throw new MalformedInputException('Cannot access a File Trailer field prior to reading an incoming File Trailer line.');
        }
    }

    protected function processHeader(string $recordCode, string $line): void
    {
        $this->currentRecordCode = $recordCode;
        $this->headerParser = new FileHeaderParser();
        $this->headerParser->pushLine($line);
    }

    protected function processTrailer(string $recordCode, string $line): void
    {
        $this->currentRecordCode = $recordCode;
        $this->trailerParser = new FileTrailerParser(
            physicalRecordLength: $this->getPhysicalRecordLength()
        );
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
                $this->currentChild = new GroupRecord(
                    physicalRecordLength: $this->getPhysicalRecordLength()
                );
                $this->groups[] = $this->currentChild;
                $this->currentChild->parseLine($line);
                break;

            default:
                $this->currentChild->parseLine($line);
                break;

        }
    }

}
