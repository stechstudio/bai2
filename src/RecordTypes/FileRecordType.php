<?php
namespace STS\Bai2\RecordTypes;

use STS\Bai2\RecordTypes\AbstractContainerRecordType;
use STS\Bai2\RecordTypes\GroupRecordType;

class FileRecordType extends AbstractContainerRecordType
{

    public function parseLine(string $line): void
    {
        switch ($this->getRecordTypeCode($line)) {
            // File Header
            case '01':
                // TODO(zmd): parse? hahaha, yah right!
                $this->records[] = $line;
                break;

            // File Trailer
            case '99':
                $this->finalize($line);
                break;

            // Group Header
            case '02':
                $this->currentChild = new GroupRecordType($line);
                $this->records[] = $this->currentChild;
                break;

            // Group Trailer
            case '98':
                $this->assertCurrentChild()->finalize($line);
                $this->currentChild = null;
                break;

            // Continuation
            case '88':
                $this->continue($line);
                break;

            // Record type must be Group's problem
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

}
