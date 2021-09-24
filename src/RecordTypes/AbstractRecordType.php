<?php
namespace STS\Bai2\RecordTypes;

abstract class AbstractRecordType
{

    public $records = [];

    public function __construct(?string $line)
    {
        if ($line) {
            $this->parseLine($line);
        }
    }

    protected function getRecordTypeCode(string $line): string
    {
        return substr($line, 0, 2);
    }

    public function toArray(): array
    {
        return array_map(
            fn($ele) => gettype($ele) == 'string' ? $ele : $ele->toArray(),
            $this->records
        );
    }

}
