<?php

namespace STS\Bai2\Parsers;

class LineBuffer
{

    protected int $finalPoint;

    protected int $cursor = -1;

    protected array $prevCursors = [];

    public function __construct(protected string $line)
    {
        $this->finalPoint = strlen($line) - 1;
    }

    public static function stringSlice(
        string $input,
        int $beginIndex,
        ?int $endIndex = null
    ): string {
        if (is_null($endIndex)) {
            return substr($input, $beginIndex);
        }

        // TODO(zmd): decide if we want to allow negative slice indices, etc.;
        //   if so, make sure we're doing the right thing; if not, raise when
        //   non-positive arguments given or when $endIndex is not greater than
        //   or equal to $beginIndex.
        $offset = $endIndex - $beginIndex;
        return substr($input, $beginIndex, $offset);
    }

    public function next(): self
    {
        $this->prevCursors[] = $this->cursor;

        if ($this->cursor < 0) {
            $this->cursor = 0;
        } else if ($next = strpos($this->line, ',', $this->cursor)) {
            $this->cursor = $next + 1;
        } else {
            $this->cursor = $this->finalPoint;
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
        // TODO(zmd): clean this mess up young man! null coalescing FTW?
        $end = $this->seek(',');
        if (is_null($end)) {
            $end = $this->seek('/');

            if (is_null($end)) {
                throw new \Exception('Tried to access last field on unterminated input line.');
            }
        }

        return self::stringSlice($this->line, $this->cursor, $end);
    }

    // TODO(zmd): can we tighten things down and disallow returning null?
    public function textField(): ?string
    {
        $value = self::stringSlice($this->line, $this->cursor);
        $this->cursor = $this->finalPoint;

        if ($value == '/') {
            return '';
        }

        return $value;
    }

    public function isEndOfLine(): bool
    {
        return $this->cursor == $this->finalPoint;
    }

    protected function seek(string $needle): ?int {
        $found = strpos($this->line, $needle, $this->cursor);

        if ($found === false) {
            return null;
        }

        return $found;
    }

}
