<?php
namespace STS\Bai2;

use STS\Bai2\RecordTypes\{
    Account,
    File,
    Group,
    Transaction
};

class Bai2
{

    public $root = null;

    public function parseLine(string $line): void
    {
        if ($root) {
            $root->parseLine($line);
        } else {
            $root = new File($line);
        }
    }

    public function toArray(): array
    {
        return $this->root->toArray();
    }

}
