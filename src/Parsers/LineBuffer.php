<?php

namespace STS\Bai2\Parsers;

class LineBuffer
{

    // TODO(zmd): in retrospect, I don't like this wrapper class; remove it at
    //   earliest convenience.
    protected SliceableString $line;

    protected int $cursor = -1;

    protected array $prevCursors = [];

    public function __construct(string $line)
    {
        $this->line = new SliceableString($line);
    }

    public function next(): self
    {
        $this->prevCursors[] = $this->cursor;

        if ($this->cursor < 0) {
            $this->cursor = 0;
        } else if ($next = $this->line->pos(',', $this->cursor)) {
            $this->cursor = $next + 1;
        } else {
            $this->cursor = $this->line->len() - 1;
        }

        return $this;
    }

    public function prev(): self
    {
        $this->cursor = array_pop($this->prevCursors) ?? -1;

        return $this;
    }

    // TODO(zmd): can we tighten things down and disallow returning null?
    public function field(): ?string
    {
        // TODO(zmd): clean this mess up young man!
        $end = $this->line->pos(',', $this->cursor);
        if ($end === false) {
            $end = $this->line->pos('/', $this->cursor);

            if ($end === false) {
                throw new \Exception('Tried to access last field on unterminated input line.');
            }
        }

        return $this->line->slice($this->cursor, $end);
    }

    // TODO(zmd): can we tighten things down and disallow returning null?
    public function textField(): ?string
    {
        $value = $this->line->slice($this->cursor);
        $this->cursor = $this->line->len() - 1;

        if ($value == '/') {
            return '';
        }

        return $value;
    }

    public function isEndOfLine(): bool
    {
        return $this->cursor == $this->line->len() -1;
    }

}
