<?php

namespace STS\Bai2\Parsers;

class LineBuffer
{

    protected int $endOfLine;

    protected bool $textTaken = false;

    protected int $cursor = 0;

    public function __construct(protected string $line)
    {
        $this->endOfLine = strlen($line) - 1;
    }

    public function next(): self
    {
        if ($this->textTaken) {
            $this->cursor = $this->endOfLine;
        } else if ($next = $this->seek(',')) {
            $this->cursor = $next + 1;
        } else {
            $this->cursor = $this->endOfLine;
        }

        return $this;
    }

    public function field(): string
    {
        return $this->slice($this->cursor, $this->findFieldEnd());
    }

    public function textField(): string
    {
        $value = $this->slice($this->cursor);
        $this->textTaken = true;

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
            throw new \Exception('Cannot access last (non-text) field on unterminated input line.');
        }

        return $end;
    }

}
