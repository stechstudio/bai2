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

    protected function fetch(int $index): ?string
    {
        // TODO(zmd): perfect place for null-coalescing assignment op
        //    $this->cache[$index] ??= ...
        if (!array_key_exists($index, $this->cache)) {
            $this->cache[$index] = $this->buffer->next();
        }

        return $this->cache[$index];
    }

}
