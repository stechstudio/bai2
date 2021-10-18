<?php

namespace STS\Bai2\Records;

use STS\Bai2\Bai2;

use STS\Bai2\Parsers\MultilineParser;

class FileRecord extends AbstractEnvelopeRecord
{

    protected ?array $headerFields = null;

    protected ?MultilineParser $headerParser = null;

    protected ?array $trailerFields = null;

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
        return $this->headerFields()[1];
    }

    public function getReceiverIdentification(): string
    {
        return $this->headerFields()[2];
    }

    public function getFileCreationDate(): string
    {
        return $this->headerFields()[3];
    }

    public function getFileCreationTime(): string
    {
        return $this->headerFields()[4];
    }

    public function getFileIdentificationNumber(): string
    {
        return $this->headerFields()[5];
    }

    public function getPhysicalRecordLength(): ?int
    {
        return $this->normalizeEmptyString($this->headerFields()[6]);
    }

    public function getBlockSize(): ?int
    {
        return $this->normalizeEmptyString($this->headerFields()[7]);
    }

    public function getVersionNumber(): string
    {
        return $this->headerFields()[8];
    }

    public function getFileControlTotal(): int
    {
        // TODO(zmd): validate field present (this is not optional)
        return (int) $this->trailerFields()[1];
    }

    public function getNumberOfGroups(): int
    {
        // TODO(zmd): validate field present (this is not optional)
        return (int) $this->trailerFields()[2];
    }

    public function getNumberOfRecords(): int
    {
        // TODO(zmd): validate field present? (this is not optional?)
        return (int) $this->trailerFields()[3];
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

    protected function headerFields(): array
    {
        // TODO(zmd): error handling if $headerParser was never initialized?
        return $this->headerFields ??= [
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
    }

    protected function parseContinuation(string $line): void
    {
        // TODO(zmd): error handling if $headerTrailer and/or $trailerParser
        //   never get initialized?
        if ($this->trailerParser) {
            $this->pushTrailerLine($line);
        } else {
            $this->pushHeaderLine($line);
        }
    }

    protected function trailerFields(): array
    {
        // TODO(zmd): error handling if $trailerParser was never initialized?
        return $this->trailerFields ??= [
            $_recordCode,
            $fileControlTotal,
            $numberOfGroups,
            $numberOfRecords
        ] = $this->trailerParser->drop(4);
    }

    private function normalizeEmptyString(string $s): ?string
    {
        return $s === '' ? null : $s;
    }

}
