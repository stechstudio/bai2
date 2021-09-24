<?php
namespace STS\Bai2\RecordTypes;

use STS\Bai2\RecordTypes\GroupRecordType;

class FileRecordType
{

    public $records = [];

    public ?GroupRecordType $currentGroup = null;

    public function __construct(?string $line)
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

    protected function getRecordTypeCode(string $line): string
    {
        return substr($line, 0, 2);
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

    public function toArray(): array
    {
        return array_map(
            fn($ele) => gettype($ele) == 'string' ? $ele : $ele->toArray(),
            $this->records
        );
    }

}
