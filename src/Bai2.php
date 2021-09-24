<?php
namespace STS\Bai2;

use STS\Bai2\RecordTypes\{
    AccountRecordType,
    FileRecordType,
    GroupRecordType,
    TransactionRecordType
};

class Bai2
{

    public $root = null;

    public function parseLine(string $line): void
    {
        if ($this->root) {
            $this->root->parseLine($line);
        } else {
            $this->root = new FileRecordType($line);
        }
    }

    public function toArray(): array
    {
        return $this->root->toArray();
    }

}
