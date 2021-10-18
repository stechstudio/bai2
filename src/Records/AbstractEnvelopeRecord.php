<?php

namespace STS\Bai2\Records;

abstract class AbstractEnvelopeRecord extends AbstractRecord
{

    public ?AbstractRecord $currentChild = null;

    protected function activeChild(): bool
    {
        if ($this->currentChild) {
            return !$this->currentChild->getFinalized();
        }

        return false;
    }

    protected function extantCurrentChild(): AbstractRecord
    {
        if ($this->activeChild()) {
            return $this->currentChild;
        }

        throw new \Exception('$currentChild was unexpectedly null or finalized');
    }

    protected function parseOrDelegateContinuation(string $line): void
    {
        if ($this->activeChild()) {
            $this->delegateToChild($line);
        } else {
            $this->parseContinuation($line);
        }
    }

    protected function delegateToChild(string $line): void
    {
        if (!$this->activeChild()) {
            $this->newChild();
        }

        $this->currentChild->parseLine($line);
    }

    abstract protected function newChild(): void;

}
