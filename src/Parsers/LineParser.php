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

    public function take(int $numToTake = 0): array|string|null
    {
        if ($numToTake) {
            return $this->takeN($numToTake);
        }

        return $this->takeOne();
    }

    public function takeOne(): ?string
    {
        return $this->next();
    }

    public function takeN(int $numToTake): array
    {
        $slice = [];
        for (; $numToTake; --$numToTake) {
            // TODO(zmd): we probably want to throw if user tries to take more
            //   than what is available to take.
            $slice[] = $this->next();
        }
        return $slice;
    }

    public function takeText(): ?string
    {
        // TODO(zmd): this is so terrible; refactor LineParserBuffer such that
        //   this becomes unnecessary
        $this->buffer = new LineParserBuffer($this->line, $this->cursor + 2);
        for ($counter = $this->cursor + 1; $counter; --$counter) {
            $this->buffer->next();
        }
        return $this->take();
    }

    protected function next(): ?string
    {
        $this->cursor++;
        return $this->fetch($this->cursor);
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
