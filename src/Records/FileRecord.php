<?php

declare(strict_types=1);

namespace STS\Bai2\Records;

use STS\Bai2\Bai2;

use STS\Bai2\Parsers\FileHeaderParser;
use STS\Bai2\Parsers\FileTrailerParser;

use STS\Bai2\Exceptions\MalformedInputException;
use STS\Bai2\Exceptions\InvalidTypeException;
use STS\Bai2\Exceptions\ParseException;

class FileRecord
{

    protected FileHeaderParser $headerParser;

    protected FileTrailerParser $trailerParser;

    protected array $groups = [];

    protected GroupRecord $currentChild;

    public function parseLine(string $line): void
    {
        match ($recordCode = Bai2::recordTypeCode($line)) {
            '01' => $this->processHeader($line),
            '88' => $this->processContinuation($line),
            '99' => $this->processTrailer($line),
            default => $this->processChildRecord($recordCode, $line)
        };
    }

    public function toArray(): array
    {
        try {
            $headerArray = $this->headerParser->toArray();
        } catch (\Error) {
            throw new MalformedInputException('Cannot access a File Header field prior to reading an incoming File Header line.');
        } catch (InvalidTypeException $e) {
            throw new MalformedInputException("Encountered issue trying to parse File Header Field. {$e->getMessage()}");
        } catch (ParseException) {
            throw new MalformedInputException('Cannot access a File Header field from an incomplete or malformed File Header line.');
        }

        try {
            $trailerArray = $this->trailerParser->toArray();
        } catch (\Error) {
            throw new MalformedInputException('Cannot access a File Trailer field prior to reading an incoming File Trailer line.');
        } catch (InvalidTypeException $e) {
            throw new MalformedInputException("Encountered issue trying to parse File Trailer Field. {$e->getMessage()}");
        } catch (ParseException) {
            throw new MalformedInputException('Cannot access a File Trailer field from an incomplete or malformed File Trailer line.');
        }

        $groupsArray = [
            'groups' => array_map(
                fn($group) => $group->toArray(),
                $this->groups
            )
        ];

        $combinedArray = $headerArray + $trailerArray + $groupsArray;
        unset($combinedArray['recordCode']);

        return $combinedArray;
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
        } catch (InvalidTypeException $e) {
            throw new MalformedInputException("Encountered issue trying to parse File Header Field. {$e->getMessage()}");
        } catch (ParseException) {
            throw new MalformedInputException('Cannot access a File Header field from an incomplete or malformed File Header line.');
        }
    }

    protected function trailerField(string $fieldKey): null|string|int
    {
        try {
            return $this->trailerParser[$fieldKey];
        } catch (\Error) {
            throw new MalformedInputException('Cannot access a File Trailer field prior to reading an incoming File Trailer line.');
        } catch (InvalidTypeException $e) {
            throw new MalformedInputException("Encountered issue trying to parse File Trailer Field. {$e->getMessage()}");
        } catch (ParseException) {
            throw new MalformedInputException('Cannot access a File Trailer field from an incomplete or malformed File Trailer line.');
        }
    }

    protected function processHeader(string $line): void
    {
        $this->headerParser = new FileHeaderParser();
        $this->headerParser->pushLine($line);
    }

    protected function processTrailer(string $line): void
    {
        $this->trailerParser = new FileTrailerParser(
            physicalRecordLength: $this->getPhysicalRecordLength()
        );
        $this->trailerParser->pushLine($line);
    }

    protected function processContinuation(string $line): void
    {
        if (isset($this->trailerParser)) {
            $this->trailerParser->pushLine($line);
        } else if (isset($this->currentChild)) {
            $this->currentChild->parseLine($line);
        } else if (isset($this->headerParser)) {
            $this->headerParser->pushLine($line);
        } else {
            throw new MalformedInputException('Cannot process a continuation without first processing something that can be continued.');
        }
    }

    protected function processChildRecord(string $recordCode, string $line): void
    {
        if ($recordCode == '02') {
            $this->currentChild = new GroupRecord(
                physicalRecordLength: $this->getPhysicalRecordLength()
            );
            $this->groups[] = $this->currentChild;
        }

        try {
            $this->currentChild->parseLine($line);
        } catch (\Error) {
            throw new MalformedInputException('Cannot process Group Trailer, Account-related, or Transaction-related line before processing the Group Header line.');
        }
    }

}
