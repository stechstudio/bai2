<?php

namespace STS\Bai2\Parsers;

class LineBuffer
{

    protected int $endOfLine;

    protected bool $textTaken = false;

    protected int $cursor = 0;

    protected array $prevCursors = [];

    public function __construct(protected string $line)
    {
        $this->endOfLine = strlen($line) - 1;
    }

    public function next(): self
    {
        // TODO(zmd): this needs tested, and possibly a custom exception class
        if ($this->isEndOfLine()) {
            throw new \Exception('Cannot move past the end of the line.');
        }

        $this->pushCursor();
        $this->nextCursor();

        return $this;
    }

    public function prev(): self
    {
        // TODO(zmd): this needs tested, and possibly a custom exception class
        if ($this->isBeginningOfLine()) {
            throw new \Exception('Cannot move past the beginning of the line.');
        }

        $this->popCursor();
        $this->textTaken = false;

        return $this;
    }

    public function field(): string
    {
        return $this->slice($this->cursor, $this->findFieldEnd());
    }

    public function textField(): string
    {
        // TODO(zmd): this needs tested, and possibly a custom exception class
        if ($this->textTaken) {
            throw new \Exception('Cannot take text more than once in a row.');
        }

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

    public function isBeginningOfLine(): bool
    {
        return $this->cursor == 0;
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

        // TODO(zmd): this needs tested, and possibly a custom exception class
        if (is_null($end)) {
            throw new \Exception('Cannot access last (non-text) field on unterminated input line.');
        }

        return $end;
    }

    protected function pushCursor(): void
    {
        $this->prevCursors[] = $this->cursor;
    }

    protected function nextCursor(): void
    {
        // TODO(zmd): can we use a match expression here instead of if-else-if
        //   chain here? (And would it be easier to understand if we did?)
        if ($this->textTaken) {
            $this->cursor = $this->endOfLine;
        } else if ($next = $this->seek(',')) {
            $this->cursor = $next + 1;
        } else {
            $this->cursor = $this->endOfLine;
        }
    }

    protected function popCursor(): void
    {
        // TODO(zmd): now that we carefully guard ::next() and ::prev(), I
        //   don't think we need to coalesce the null here any longer...
        $this->cursor = array_pop($this->prevCursors) ?? 0;
    }

}
