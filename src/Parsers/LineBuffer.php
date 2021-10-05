<?php

namespace STS\Bai2\Parsers;

class LineBuffer
{

    protected int $sliceStart = 0;

    protected int $sliceEnd = 0;

    public function __construct(protected string $line)
    {
    }

    public function next(): self
    {
        [$this->sliceStart, $this->sliceEnd] = $this->findNextSlice();

        return $this;
    }

    public function prev(): self
    {
        [$this->sliceStart, $this->sliceEnd] = $this->findPrevSlice();

        return $this;
    }

    // TODO(zmd): can we tighten things down and disallow returning null?
    public function field(): ?string
    {
        // TODO(zmd): implement me!
    }

    // TODO(zmd): can we tighten things down and disallow returning null?
    public function textField(): ?string
    {
        // TODO(zmd): implement me!
    }

    public function isEndOfLine(): bool
    {
        // TODO(zmd): implement me!
    }

    protected function findNextSlice(): array
    {
        // TODO(zmd): implement me!
    }

    protected function findPrevSlice(): array
    {
        // TODO(zmd): implement me!
    }

}
