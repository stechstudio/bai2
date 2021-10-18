<?php

namespace STS\Bai2\Records;

use STS\Bai2\Bai2;

class AccountRecord extends AbstractEnvelopeRecord
{

    public function parseLine(string $line): void
    {
        switch (Bai2::recordTypeCode($line)) {
            case '03':
                $this->parseIdentifier($line);
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

    protected function newChild(): void
    {
        $this->currentChild = new TransactionRecord();
        $this->records[] = $this->currentChild;
    }

    protected function parseIdentifier(string $line): void
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
