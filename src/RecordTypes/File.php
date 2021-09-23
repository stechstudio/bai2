<?php
namespace STS\Bai2\RecordTypes;

use Group;

class File
{

    public $rawUnparsed = [];

    public $records = [];

    public Group? $currentGroup = null;

    public function __construct(string? $line)
    {
        if ($line) {
            $this->parseLine($line);
        }
    }

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
                $this->currentGroup = new Group($line);
                $this->records[] = $this->currentGroup;
                break;

            // Group Trailer
            case '98':
                $this->currentGroup->finalize($line);
                $this->currentGroup = null;
                break;

            // Continuation
            case '88':
                if ($this->currentGroup) {
                    $this->currentGroup->parseLine($line);
                } else {
                    $this->continue($line);
                }
                break;

            // Record type must be Group's problem
            default:
                if ($this->currentGroup) {
                    $this->currentGroup->parseLine($line);
                } else {
                    // TODO(zmd): more appropriate message, please.
                    throw new Exception('lolwut?');
                }
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

    protected function getRecordTypeCode(string $line): string
    {
        return substr($line, 0, 2);
    }

    public function toArray(): array
    {
        // TODO(zmd): implement me!
    }

}
