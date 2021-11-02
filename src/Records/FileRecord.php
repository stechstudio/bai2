<?php

namespace STS\Bai2\Records;

use STS\Bai2\Bai2;

use STS\Bai2\Exceptions\ExtantAssertionException;

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
                ($this->headerParser ??= new FileHeaderParser())->pushLine($line);
                break;
            case '88':
                $this->parseOrDelegateContinuation($line);
                break;
            case '99':
                ($this->trailerParser ??= new FileTrailerParser())->pushLine($line);
                break;
            default:
                $this->delegateToChild($line);
                break;
        }
    }

    public function getSenderIdentification(): string
    {
        return $this->extantHeaderParser()['senderIdentification'];
    }

    public function getReceiverIdentification(): string
    {
        return $this->extantHeaderParser()['receiverIdentification'];
    }

    public function getFileCreationDate(): string
    {
        return $this->extantHeaderParser()['fileCreationDate'];
    }

    public function getFileCreationTime(): string
    {
        return $this->extantHeaderParser()['fileCreationTime'];
    }

    public function getFileIdentificationNumber(): string
    {
        return $this->extantHeaderParser()['fileIdentificationNumber'];
    }

    public function getPhysicalRecordLength(): ?int
    {
        return $this->extantHeaderParser()['physicalRecordLength'];
    }

    public function getBlockSize(): ?int
    {
        return $this->extantHeaderParser()['blockSize'];
    }

    public function getVersionNumber(): string
    {
        return $this->extantHeaderParser()['versionNumber'];
    }

    public function getFileControlTotal(): int
    {
        return $this->extantTrailerParser()['fileControlTotal'];
    }

    public function getNumberOfGroups(): int
    {
        return $this->extantTrailerParser()['numberOfGroups'];
    }

    public function getNumberOfRecords(): int
    {
        return $this->extantTrailerParser()['numberOfRecords'];
    }

    public function groups(): array
    {
        // TODO(zmd): implement me
        return [];
    }

    protected function extantHeaderParser(): FileHeaderParser
    {
        if ($this->headerParser) {
            return $this->headerParser;
        }

        throw new ExtantAssertionException('Tried to read File Header fields before File Header lines processed.');
    }

    protected function extantTrailerParser(): FileTrailerParser
    {
        if ($this->trailerParser) {
            return $this->trailerParser;
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
            $this->trailerParser->pushLine($line);
        } else {
            $this->headerParser->pushLine($line);
        }
    }

}
