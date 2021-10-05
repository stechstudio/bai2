<?php

namespace STS\Bai2\Parsers;

class LineParser
{

    protected LineParserBuffer $buffer;

    public function __construct(string $line)
    {
        $this->buffer = new LineParserBuffer($line);
    }

    public function peek(): ?string
    {
        $value = $this->buffer->next->val();
        $this->buffer->prev();

        return $value;
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

    // TODO(zmd): we probably want to throw if user tries to shift beyond end
    //   of buffer?
    public function shift(): ?string
    {
        return $this->buffer->next()->val();
    }

    public function shiftText(): ?string
    {
        return $this->buffer->next()->text();
    }

    // TODO(zmd): ponder whether we want to keep this, and if we do write
    //   proper tests to cover it.
    public function isEndOfLine(): bool
    {
        return $this->buffer->isEndOfLine();
    }

}
