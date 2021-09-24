<?php
namespace STS\Bai2\RecordTypes;

use STS\Bai2\RecordTypes\AbstractRecordType;

class TransactionRecordType extends AbstractRecordType
{

    public function parseLine(string $line): void
    {
        switch ($this->getRecordTypeCode($line)) {
            // Transaction
            case '16':
                // TODO(zmd): parse? hahaha, yah right!
                $this->records[] = $line;
                break;

            // Continuation
            case '88':
                $this->continue($line);
                break;

            // Record type must be a problem
            default:
                // TODO(zmd): pretty sure we should never reach this, as it
                //   means we've encountered an invalid record type!
                // TODO(zmd): more appropriate message, please.
                throw new \Exception('lolwut?');
                break;
        }
    }

    public function finalize(string $line): void
    {
        // TODO(zmd): parse? hahaha, yah right!
        $this->records[] = $line;
    }

    public function continue(string $line): void
    {
        // TODO(zmd): parse? hahaha, yah right!
        $this->records[] = $line;
    }

}
