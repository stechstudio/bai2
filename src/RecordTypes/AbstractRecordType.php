<?php
namespace STS\Bai2\RecordTypes;

abstract class AbstractRecordType
{

    protected bool $finalized = false;

    protected $records = [];

    public function __construct(?string $line = null)
    {
        if ($line) {
            $this->parseLine($line);
        }
    }

    public abstract function parseLine(string $line): void;

    protected function getRecordTypeCode(string $line): string
    {
        return substr($line, 0, 2);
    }

    protected function setFinalized(bool $finalized): void
    {
        $this->finalized = $finalized;
    }

    public function getFinalized(): bool
    {
        return $this->finalized;
    }

    public function toArray(): array
    {
        return array_map(
            fn($ele) => gettype($ele) == 'string' ? $ele : $ele->toArray(),
            $this->records
        );
    }

}
