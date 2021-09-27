<?php
namespace STS\Bai2\RecordTypes;

use STS\Bai2\RecordTypes\AbstractRecordType;

class TransactionRecordType extends AbstractRecordType
{

    public function parseLine(string $line): void
    {
        switch ($this->getRecordTypeCode($line)) {
            case '16':
                $this->parseDetail($line);
                break;
            case '88':
                $this->parseContinuation($line);
                break;
            default:
                throw new \Exception('Unknown record type.');
                break;
        }
    }

    protected function parseDetail(string $line): void
    {
        // TODO(zmd): parse? hahaha, yah right!
        $this->records[] = $line;
    }

    protected function parseContinuation(string $line): void
    {
        // TODO(zmd): parse? hahaha, yah right!
        $this->records[] = $line;
    }

}
