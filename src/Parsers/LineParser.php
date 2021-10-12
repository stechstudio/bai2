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

    // TODO(zmd): we probably want to throw if user tries to drop more than
    //   what is available in the buffer?
    public function drop(int $numToDrop): array
    {
        $slice = [];
        for (; $numToDrop; --$numToDrop) {
            $slice[] = $this->shift();
        }
        return $slice;
    }

    // TODO(zmd): we probably want to throw if user tries to shift more than
    //   what is available in the buffer?
    public function shift(): string
    {
        $field = $this->buffer->field();
        $this->buffer->eat();
        return $field;
    }

    // TODO(zmd): we probably want to throw if user tries to shiftText more
    //   than what is available in the buffer?
    public function shiftText(): string
    {
        $field = $this->buffer->textField();
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
