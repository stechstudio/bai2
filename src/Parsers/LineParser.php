<?php

namespace STS\Bai2\Parsers;

class LineParser
{

    protected LineBuffer $buffer;

    public function __construct(string $line)
    {
        $this->buffer = new LineBuffer($line);
    }

    public function peek(): string
    {
        return $this->buffer->field();
    }

    public function drop(int $numToDrop): array
    {
        $slice = [];
        for (; $numToDrop; --$numToDrop) {
            $slice[] = $this->shift();
        }
        return $slice;
    }

    public function shift(): string
    {
        $field = $this->buffer->field();
        $this->buffer->eat();
        return $field;
    }

    public function shiftText(): string
    {
        $field = $this->buffer->textField();
        $this->buffer->eat();
        return $field;
    }

    public function shiftContinuedText(): string
    {
        $field = $this->buffer->continuedTextField();
        $this->buffer->eat();
        return $field;
    }

    public function hasMore(): bool
    {
        return !$this->isEndOfLine();
    }

    public function isEndOfLine(): bool
    {
        return $this->buffer->isEndOfLine();
    }

}
