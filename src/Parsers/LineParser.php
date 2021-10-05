<?php

namespace STS\Bai2\Parsers;

class LineParser
{

    protected LineParserBuffer $buffer;

    // TODO(zmd): this is so terrible; refactor LineParserBuffer such that this
    //   becomes unnecessary
    protected string $line;

    protected array $cache = [];

    protected int $cursor = -1;

    public function __construct(string $line)
    {
        $this->buffer = new LineParserBuffer($line);
        // TODO(zmd): this is so terrible; refactor LineParserBuffer such that
        //   this becomes unnecessary
        $this->line = $line;
    }

    public function peek(): ?string
    {
        return $this->fetch($this->cursor + 1);
    }

    public function drop(int $numToDrop): array
    {
        $slice = [];
        for (; $numToDrop; --$numToDrop) {
            // TODO(zmd): we probably want to throw if user tries to drop more
            //   than what is available to drop.
            $slice[] = $this->shift();
        }
        return $slice;
    }

    public function shift(): ?string
    {
        $this->cursor++;
        return $this->fetch($this->cursor);
    }

    public function shiftText(): ?string
    {
        // TODO(zmd): this is so terrible; refactor LineParserBuffer such that
        //   this becomes unnecessary
        $this->buffer = new LineParserBuffer($this->line, $this->cursor + 2);
        for ($counter = $this->cursor + 1; $counter; --$counter) {
            $this->buffer->next();
        }
        return $this->shift();
    }

    // TODO(zmd): ponder whether we want to keep this, and if we do write
    //   proper tests to cover it.
    public function isEndOfLine(): bool
    {
        return $this->buffer->isEndOfLine();
    }

    protected function fetch(int $index): ?string
    {
        // TODO(zmd): perfect place for null-coalescing assignment op
        //    $this->cache[$index] ??= ...
        if (!array_key_exists($index, $this->cache)) {
            $next = $this->buffer->next();

            if (is_null($next)) {
                return null;
            } else {
                $this->cache[$index] = $next;
            }
        }

        return $this->cache[$index];
    }

}
