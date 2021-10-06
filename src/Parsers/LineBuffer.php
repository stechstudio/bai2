<?php

namespace STS\Bai2\Parsers;

class LineBuffer
{

    protected int $endOfLine;

    protected int $cursor = -1;

    protected array $prevCursors = [];

    public function __construct(protected string $line)
    {
        $this->endOfLine = strlen($line) - 1;
    }

    public function next(): self
    {
        $this->pushCursor();
        $this->nextCursor();

        return $this;
    }

    public function prev(): self
    {
        $this->popCursor();
        return $this;
    }

    // TODO(zmd): can we tighten things down and disallow returning null?
    public function field(): ?string
    {
        return $this->slice($this->cursor, $this->findFieldEnd());
    }

    // TODO(zmd): can we tighten things down and disallow returning null?
    public function textField(): ?string
    {
        $value = $this->slice($this->cursor);
        $this->cursor = $this->endOfLine;

        if ($value == '/') {
            return '';
        }

        return $value;
    }

    public function isEndOfLine(): bool
    {
        return $this->cursor == $this->endOfLine;
    }

    protected function seek(string $needle): ?int {
        $found = strpos($this->line, $needle, $this->cursor);

        if ($found === false) {
            return null;
        }

        return $found;
    }

    protected function slice(int $beginIndex, ?int $endIndex = null): string
    {
        if (is_null($endIndex)) {
            return substr($this->line, $beginIndex);
        }

        $offset = $endIndex - $beginIndex;
        return substr($this->line, $beginIndex, $offset);
    }

    protected function findFieldEnd(): int
    {
        $end = $this->seek(',') ?? $this->seek('/');

        if (is_null($end)) {
            throw new \Exception('Tried to access last field on unterminated input line.');
        }

        return $end;
    }

    protected function pushCursor(): void
    {
        $this->prevCursors[] = $this->cursor;
    }

    protected function nextCursor(): void
    {
        if ($this->cursor < 0) {
            $this->cursor = 0;
        } else if ($next = $this->seek(',')) {
            $this->cursor = $next + 1;
        } else {
            $this->cursor = $this->endOfLine;
        }
    }

    protected function popCursor(): void
    {
        $this->cursor = array_pop($this->prevCursors) ?? -1;
    }

}
