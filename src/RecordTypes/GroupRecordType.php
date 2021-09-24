<?php
namespace STS\Bai2\RecordTypes;

use STS\Bai2\RecordTypes\AccountRecordType;

class GroupRecordType
{

    public $records = [];

    public ?AccountRecordType $currentAccount = null;

    public function __construct(?string $line)
    {
        if ($line) {
            $this->parseLine($line);
        }
    }

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
                $this->currentAccount = new AccountRecordType($line);
                $this->records[] = $this->currentAccount;
                break;

            // Account Trailer
            case '49':
                $this->assertCurrentAccount()->finalize($line);
                $this->currentAccount = null;
                break;

            // Continuation
            case '88':
                $this->continue($line);
                break;

            // Record type must be Account's problem
            default:
                $this->assertCurrentAccount()->parseLine($line);
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
        if ($this->currentAccount) {
            $this->currentAccount->continue($line);
        } else {
            // TODO(zmd): parse? hahaha, yah right!
            $this->records[] = $line;
        }
    }

    protected function assertCurrentAccount(): AccountRecordType
    {
        if ($this->currentAccount) {
            return $this->currentAccount;
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
