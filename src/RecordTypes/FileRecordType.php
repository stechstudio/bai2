<?php
namespace STS\Bai2\RecordTypes;

use STS\Bai2\RecordTypes\AbstractRecordType;
use STS\Bai2\RecordTypes\GroupRecordType;

class FileRecordType extends AbstractRecordType
{

    public ?GroupRecordType $currentGroup = null;

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
                $this->currentGroup = new GroupRecordType($line);
                $this->records[] = $this->currentGroup;
                break;

            // Group Trailer
            case '98':
                $this->assertCurrentGroup()->finalize($line);
                $this->currentGroup = null;
                break;

            // Continuation
            case '88':
                $this->continue($line);
                break;

            // Record type must be Group's problem
            default:
                $this->assertCurrentGroup()->parseLine($line);
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
        if ($this->currentGroup) {
            $this->currentGroup->continue($line);
        } else {
            // TODO(zmd): parse? hahaha, yah right!
            $this->records[] = $line;
        }
    }

    protected function assertCurrentGroup(): GroupRecordType
    {
        if ($this->currentGroup) {
            return $this->currentGroup;
        }

        // TODO(zmd): more appropriate message, please.
        throw new \Exception('lolwut?');
    }

}
