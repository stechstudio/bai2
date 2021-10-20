<?php

namespace STS\Bai2\Records;

use STS\Bai2\Bai2;

use STS\Bai2\Parsers\FileHeaderParser;
use STS\Bai2\Parsers\FileTrailerParser;

class FileRecord extends AbstractEnvelopeRecord
{

    protected ?FileHeaderParser $headerParser = null;

    protected ?FileTrailerParser $trailerParser = null;

    public function parseLine(string $line): void
    {
        switch (Bai2::recordTypeCode($line)) {
            case '01':
                ($this->headerParser ??= new FileHeaderParser())->push($line);
                break;
            case '88':
                $this->parseOrDelegateContinuation($line);
                break;
            case '99':
                ($this->trailerParser ??= new FileTrailerParser())->push($line);
                break;
            default:
                $this->delegateToChild($line);
                break;
        }
    }

    public function getSenderIdentification(): string
    {
        return $this->extantHeaderParser()->offsetGet('senderIdentification');
    }

    public function getReceiverIdentification(): string
    {
        return $this->extantHeaderParser()->offsetGet('receiverIdentification');
    }

    public function getFileCreationDate(): string
    {
        return $this->extantHeaderParser()->offsetGet('fileCreationDate');
    }

    public function getFileCreationTime(): string
    {
        return $this->extantHeaderParser()->offsetGet('fiileCreationTime');
    }

    public function getFileIdentificationNumber(): string
    {
        return $this->extantHeaderParser()->offsetGet('fileIdentificationNumber');
    }

    public function getPhysicalRecordLength(): ?int
    {
        return $this->extantHeaderParser()->offsetGet('physicalRecordLength');
    }

    public function getBlockSize(): ?int
    {
        return $this->extantHeaderParser()->offsetGet('blockSize');
    }

    public function getVersionNumber(): string
    {
        return $this->extantHeaderParser()->offsetGet('versionNumber');
    }

    public function getFileControlTotal(): int
    {
        return $this->extantTrailerParser()->offsetGet('fileControlTotal');
    }

    public function getNumberOfGroups(): int
    {
        return $this->extantTrailerParser()->offsetGet('numberOfGroups');
    }

    public function getNumberOfRecords(): int
    {
        return $this->extantTrailerParser()->offsetGet('numberOfRecords');
    }

    public function groups(): array
    {
        // TODO(zmd): implement me
        return [];
    }

    protected function extantHeaderParser(): FileHeaderParser
    {
        if ($this->fileHeader) {
            return $this->fileHeader;
        }

        throw new ExtantAssertionException('Tried to read File Header fields before File Header lines processed.');
    }

    protected function extantTrailerParser(): FileTrailerParser
    {
        if ($this->fileTrailer) {
            return $this->fileTrailer;
        }

        throw new ExtantAssertionException('Tried to read File Trailer fields before File Trailer lines processed.');
    }

    protected function newChild(): void
    {
        $this->currentChild = new GroupRecord();
        $this->records[] = $this->currentChild;
    }

    protected function parseContinuation(string $line): void
    {
        // TODO(zmd): error handling if $headerTrailer and/or $trailerParser
        //   never get initialized?
        if ($this->trailerParser) {
            $this->trailerParser->push($line);
        } else {
            $this->headerParser->push($line);
        }
    }

}
