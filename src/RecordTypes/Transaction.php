<?php
namespace STS\Bai2\RecordTypes;

class Transaction
{

    public $records = [];

    public function __construct(?string $line)
    {
        if ($line) {
            $this->parseLine($line);
        }
    }

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
        // TODO(zmd): parse? hahaha, yah right!
        $this->records[] = $line;
    }

    public function toArray(): array
    {
        // TODO(zmd): implement me for real!
        return $this->records;
    }

}
