<?php

namespace STS\Bai2\Parsers;

class LineParser
{

    protected LineParserBuffer $buffer;

    protected array $cache = [];

    protected int $cursor = -1;

    public function __construct(string $line)
    {
        $this->buffer = new LineParserBuffer($line);
    }

    public function peek(): ?string
    {
        return $this->fetch($this->cursor + 1);
    }

    public function takeAll(): array
    {
        while (!is_null($this->next()))
        {
        }

        return $this->cache;
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
