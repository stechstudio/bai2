<?php
namespace STS\Bai2\RecordTypes;

abstract class AbstractRecordType
{

    protected bool $finalized = false;

    protected $records = [];

    abstract public function parseLine(string $line): void;

    abstract protected function parseContinuation(string $line): void;

    protected function setFinalized(bool $finalized): AbstractRecordType
    {
        $this->finalized = $finalized;

        return $this;
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
