<?php
namespace STS\Bai2\RecordTypes;

use STS\Bai2\RecordTypes\AbstractRecordType;
use STS\Bai2\RecordTypes\AccountRecordType;

class GroupRecordType extends AbstractRecordType
{

    public ?AccountRecordType $currentChild = null;

    public function parseLine(string $line): void
    {
        switch ($this->getRecordTypeCode($line)) {
            // Group Header
            case '02':
                // TODO(zmd): parse? hahaha, yah right!
                $this->records[] = $line;
                break;

            // Group Trailer
            case '98':
                $this->finalize($line);
                break;

            // Account Header
            case '03':
                $this->currentChild = new AccountRecordType($line);
                $this->records[] = $this->currentChild;
                break;

            // Account Trailer
            case '49':
                $this->assertCurrentChild()->finalize($line);
                $this->currentChild = null;
                break;

            // Continuation
            case '88':
                $this->continue($line);
                break;

            // Record type must be Account's problem
            default:
                $this->assertCurrentChild()->parseLine($line);
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

    protected function assertCurrentChild(): AccountRecordType
    {
        if ($this->currentChild) {
            return $this->currentChild;
        }

        // TODO(zmd): more appropriate message, please.
        throw new \Exception('lolwut?');
    }

}
