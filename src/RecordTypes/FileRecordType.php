<?php
namespace STS\Bai2\RecordTypes;

use STS\Bai2\RecordTypes\AbstractContainerRecordType;
use STS\Bai2\RecordTypes\GroupRecordType;

class FileRecordType extends AbstractContainerRecordType
{

    public function parseLine(string $line): void
    {
        switch ($this->getRecordTypeCode($line)) {
            case '01':
                $this->parseHeader($line);
                break;
            case '88':
                $this->parseOrDelegateContinuation($line);
                break;
            case '99':
                $this->parseTrailer($line);
                break;
            default:
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
        $this->currentChild = new GroupRecordType;
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
        $this->setFinalized(true);
    }

}
