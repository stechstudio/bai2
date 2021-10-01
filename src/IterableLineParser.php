<?php

namespace STS\Bai2;

// TODO(zmd): this will become (be renamed to) LineParser
class IterableLineParser implements \Iterator
{

    protected LineParserBuffer $buffer;

    protected ?string $current;

    protected int $index;

    public function __construct(
        protected string $line,
        protected int $numFields = 0
    ) {
        $this->rewind();
    }

    public function current(): string
    {
        return $this->current;
    }

    public function key(): int
    {
        return $this->index;
    }

    public function next(): void
    {
        $this->index++;
        $this->current = $this->buffer->next();
    }

    public function rewind(): void
    {
        $this->index = 0;
        $this->buffer = new LineParserBuffer($this->line, $this->numFields);
        $this->current = $this->buffer->next();
    }

    public function valid(): bool
    {
        return $this->buffer->valid();
    }

    public function setNumFields(int $numFields): self
    {
        $this->numFields = $numFields;
        return $this;
    }

}
