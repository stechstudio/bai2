<?php

namespace STS\Bai2\Parsers;

class LineBuffer
{

    protected int $cursor = 0;

    public function __construct(protected string $line)
    {
    }

    public function next(): self
    {
        // TODO(zmd): implement me!
        return $this;
    }

    public function prev(): self
    {
        // TODO(zmd): implement me!
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

}
