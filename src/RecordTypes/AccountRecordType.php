<?php
namespace STS\Bai2\RecordTypes;

use STS\Bai2\RecordTypes\AbstractContainerRecordType;
use STS\Bai2\RecordTypes\TransactionRecordType;

class AccountRecordType extends AbstractContainerRecordType
{

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
                $this->currentChild = new TransactionRecordType($line);
                $this->records[] = $this->currentChild;
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
                $this->extantCurrentChild()->parseLine($line);
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

}
