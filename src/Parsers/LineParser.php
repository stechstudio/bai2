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
        $field = $this->buffer->next()->field();
        $this->buffer->prev();

        return $field;
    }

    // TODO(zmd): we probably want to throw if user tries to drop more than
    //   what is available in the buffer? (I think we might now, but that needs
    //   an explicit automated test)
    public function drop(int $numToDrop): array
    {
        $slice = [];
        for (; $numToDrop; --$numToDrop) {
            $slice[] = $this->shift();
        }
        return $slice;
    }

    // TODO(zmd): we probably want to throw if user tries to drop more than
    //   what is available in the buffer? (I think we might now, but that needs
    //   an explicit automated test)
    public function shift(): string
    {
        return $this->buffer->next()->field();
    }

    public function shiftText(): string
    {
        return $this->buffer->next()->textField();
    }

    public function hasMore(): bool
    {
        $endOfLine = $this->buffer->isEndOfLine();

        // if we're not yet at the end of the line, we need to know if we will
        // be after the next cursor advance (indicating there's nothing more to
        // shift off).
        if (!$endOfLine) {
            $this->buffer->next();
            $endOfLine = $this->buffer->isEndOfLine();
            $this->buffer->prev();
        }

        return !$endOfLine;
    }

}
