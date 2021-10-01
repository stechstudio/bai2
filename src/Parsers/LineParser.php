<?php

namespace STS\Bai2\Parsers;

class LineParser implements \Iterator
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

    public function reset(int $numFields): void
    {
        $this->numFields = $numFields;
        $this->rewind();
    }

    public function valid(): bool
    {
        return $this->buffer->valid();
    }

}
