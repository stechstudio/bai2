<?php
namespace STS\Bai2\RecordTypes;

use STS\Bai2\RecordTypes\AbstractContainerRecordType;
use STS\Bai2\RecordTypes\TransactionRecordType;

class AccountRecordType extends AbstractContainerRecordType
{

    public function parseLine(string $line): void
    {
        switch ($this->getRecordTypeCode($line)) {
            case '03':
                $this->parseHeader($line);
                break;
            case '88':
                $this->parseOrDelegateContinuation($line);
                break;
            case '49':
                $this->parseTrailer($line);
                break;
            default:
                // Transaction record types don't have headers and trailers;
                // any new non-continuation transaction line we reach indicates
                // a whole new transaction record that we need, so we reset our
                // current child.
                $this->currentChild = null;
                $this->delegateToChild($line);
                break;
        }
    }

    protected function parseOrDelegateContinuation(string $line): void
    {
        if ($this->activeChild()) {
            $this->delegateToChild($line);
        } else {
            $this->parseContinuation($line);
        }
    }

    protected function newChild(): void
    {
        $this->currentChild = new TransactionRecordType;
        $this->records[] = $this->currentChild;
    }

    protected function parseHeader(string $line): void
    {
        // TODO(zmd): parse? hahaha, yah right!
        $this->records[] = $line;
    }

    protected function parseContinuation(string $line): void
    {
        // TODO(zmd): parse? hahaha, yah right!
        $this->records[] = $line;
    }

    protected function parseTrailer(string $line): void
    {
        // TODO(zmd): parse? hahaha, yah right!
        $this->records[] = $line;
    }

}
