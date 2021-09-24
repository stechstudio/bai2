<?php
namespace STS\Bai2\RecordTypes;

use STS\Bai2\RecordTypes\AbstractRecordType;
use STS\Bai2\RecordTypes\TransactionRecordType;

class AccountRecordType extends AbstractRecordType
{

    public ?TransactionRecordType $currentChild = null;

    public function parseLine(string $line): void
    {
        switch ($this->getRecordTypeCode($line)) {
            // Account Header
            case '03':
                // TODO(zmd): parse? hahaha, yah right!
                $this->records[] = $line;
                break;

            // Account Trailer
            case '49':
                $this->finalize($line);
                break;

            // Transaction
            case '16':
                $this->currentTransaction = new TransactionRecordType($line);
                $this->records[] = $this->currentTransaction;
                break;

            // Continuation
            case '88':
                $this->continue($line);
                break;

            // Record type must be Transaction's problem
            default:
                // TODO(zmd): pretty sure this is unreachable (at least for a
                //   properly formed BAI2 file); this unreachable code will be
                //   eliminated shortly, however.
                $this->assertCurrentTransaction()->parseLine($line);
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
        if ($this->currentChild) {
            $this->currentChild->continue($line);
        } else {
            // TODO(zmd): parse? hahaha, yah right!
            $this->records[] = $line;
        }
    }

    protected function assertCurrentChild(): TransactionRecordType
    {
        if ($this->currentChild) {
            return $this->currentChild;
        }

        // TODO(zmd): more appropriate message, please.
        throw new \Exception('lolwut?');
    }

}
