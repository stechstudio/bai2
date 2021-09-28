<?php
namespace STS\Bai2\RecordTypes;

use STS\Bai2\Bai2;

class GroupRecordType extends AbstractEnvelopeRecordType
{

    public function parseLine(string $line): void
    {
        switch (Bai2::recordTypeCode($line)) {
            case '02':
                $this->parseHeader($line);
                break;
            case '88':
                $this->parseOrDelegateContinuation($line);
                break;
            case '98':
                $this->parseTrailer($line);
                break;
            default:
                $this->delegateToChild($line);
                break;
        }
    }

    protected function newChild(): void
    {
        $this->currentChild = new AccountRecordType;
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
