<?php

namespace STS\Bai2\Parsers;

class LineBuffer
{

    protected int $endOfLine;

    protected bool $textTaken = false;

    protected int $cursor = 0;

    public function __construct(
        protected string $line,
        public ?int $physicalRecordLength = null
    ) {
        if (!is_null($this->physicalRecordLength)) {
            $this->line = rtrim($this->line);
        }

        $this->endOfLine = strlen($this->line);
    }

    public function eat(): self
    {
        if ($this->textTaken) {
            $this->cursor = $this->endOfLine;
        } else if ($this->isEndOfLine() || $this->isEndOfLogicalRecord()) {
            throw new \Exception('Cannot advance beyond the end of the buffer.');
        } else {
            $this->cursor = $this->findFieldEnd() + 1;
        }

        return $this;
    }

    public function field(): string
    {
        if ($this->isEndOfLine() || $this->isEndOfLogicalRecord()) {
            throw new \Exception('Cannot access fields at the end of the buffer.');
        }

        return $this->readTo($this->findFieldEnd());
    }

    public function textField(): string
    {
        if ($this->isEndOfLine()) {
            throw new \Exception('Cannot access fields at the end of the buffer.');
        }

        $value = $this->readTo($this->endOfLine + 1);
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

    public function isEndOfLogicalRecord(): bool
    {
        // echo 'substr($this->line, $this->cursor - 1, 1): ';
        // var_export(substr($this->line, $this->cursor - 1, 1));
        return $this->cursor > 0 && substr($this->line, $this->cursor - 1, 1) === '/';
    }

    protected function seek(string $needle): ?int {
        $found = strpos($this->line, $needle, $this->cursor);

        if ($found === false) {
            return null;
        }

        return $found;
    }

    protected function readTo(int $endIndex = null): string
    {
        // TODO(zmd): if we don't modify things to require further calculation,
        //   then remove this intermediate variable and directly inline access
        //   to the cursor address.
        $beginIndex = $this->cursor;

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
