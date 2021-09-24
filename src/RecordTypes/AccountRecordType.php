<?php
namespace STS\Bai2\RecordTypes;

use STS\Bai2\RecordTypes\TransactionRecordType;

class AccountRecordType
{

    public $records = [];

    public ?TransactionRecordType $currentTransaction = null;

    public function __construct(?string $line)
    {
        if ($line) {
            $this->parseLine($line);
        }
    }

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
        if ($this->currentTransaction) {
            $this->currentTransaction->continue($line);
        } else {
            // TODO(zmd): parse? hahaha, yah right!
            $this->records[] = $line;
        }
    }

    protected function assertCurrentTransaction(): TransactionRecordType
    {
        if ($this->currentTransaction) {
            return $this->currentTransaction;
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
