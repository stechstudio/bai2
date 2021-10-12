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
        } else if ($this->isEndOfLine()) {
            throw new \Exception('Cannot advance beyond the end of the buffer.');
        } else {
            $this->cursor = $this->findFieldEnd() + 1;
        }

        return $this;
    }

    public function field(): string
    {
        if ($this->isEndOfLine()) {
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
        return $this->isEndOfPhysicalRecord() || $this->isEndOfLogicalRecord();
    }

    public function isEndOfPhysicalRecord(): bool
    {
        return $this->cursor == $this->endOfLine;
    }

    public function isEndOfLogicalRecord(): bool
    {
        return $this->cursor > 0 && $this->readPrevChar() === '/';
    }

    protected function readPrevChar(): string
    {
        return $this->readCharAt($this->cursor - 1);
    }

    protected function readCharAt(int $index): string
    {
        return substr($this->line, $index, 1);
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
        $offset = $endIndex - $this->cursor;
        return substr($this->line, $this->cursor, $offset);
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
