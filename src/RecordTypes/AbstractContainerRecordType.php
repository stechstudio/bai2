<?php
namespace STS\Bai2\RecordTypes;

use STS\Bai2\RecordTypes\AbstractRecordType;

abstract class AbstractContainerRecordType extends AbstractRecordType
{

    public ?AbstractRecordType $currentChild = null;

    protected function activeChild(): bool
    {
        if ($this->currentChild) {
            return !$this->currentChild->getFinalized();
        }

        return false;
    }

    protected function extantCurrentChild(): AbstractRecordType
    {
        if ($this->activeChild()) {
            return $this->currentChild;
        }

        // TODO(zmd): more appropriate message, please.
        throw new \Exception('lolwut?');
    }

    protected function delegateToChild(string $line): void
    {
        if (!$this->activeChild()) {
            $this->newChild();
        }

        $this->currentChild->parseLine($line);
    }

    protected abstract function newChild(): void;

}
