<?php
namespace STS\Bai2\RecordTypes;

class FileRecordType extends AbstractEnvelopeRecordType
{

    public function parseLine(string $line): void
    {
        switch ($this->getRecordTypeCode($line)) {
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

    public function code(): string
    {
        return 'TODO';
    }

    public function sender(): string
    {
        return 'TODO';
    }

    public function receiver(): string
    {
        return 'TODO';
    }

    public function creationDate(): string
    {
        return 'TODO';
    }

    public function creationTime(): string
    {
        return 'TODO';
    }

    public function id(): string
    {
        return 'TODO';
    }

    public function physicalRecordLength(): int
    {
        return 1227;
    }

    public function blockSize(): int
    {
        return 1227;
    }

    public function version(): int
    {
        return 1227;
    }

    public function groups(): array
    {
        return [];
    }

    protected function newChild(): void
    {
        $this->currentChild = new GroupRecordType;
        $this->records[] = $this->currentChild;
    }

    protected function parseHeader(string $line): void
    {
        // TODO(zmd): parse? hahaha, yah right!
        $this->records[] = $line;
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

}
