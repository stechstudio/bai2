<?php

namespace STS\Bai2\Parsers;

class LineParser
{

    protected LineBuffer $buffer;

    public function __construct(string $line)
    {
        $this->buffer = new LineBuffer($line);
    }

    public function peek(): ?string
    {
        $field = $this->buffer->next()->field();
        $this->buffer->prev();

        return $field;
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
        return $this->buffer->next()->field();
    }

    public function shiftText(): ?string
    {
        return $this->buffer->next()->textField();
    }

    // TODO(zmd): ponder whether we want to keep this, and if we do write
    //   proper tests to cover it.
    public function isEndOfLine(): bool
    {
        // TODO(zmd): this is so much yuck; pretty sure I'm almost to the point
        //   where I can reliably use null as indicator of end of the line and
        //   not use this special method anymore.
        //
        //   Otherwise we need a ::hasNext() or ::peekEndOfLine() or something.
        $this->buffer->next();
        $endOfLine = $this->buffer->isEndOfLine();
        $this->buffer->prev();
        return $endOfLine;
    }

}
