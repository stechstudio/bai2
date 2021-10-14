<?php

namespace STS\Bai2\Parsers;

class LineBuffer
{

    protected int $endOfLine;

    protected bool $textTaken = false;

    protected int $cursor = 0;

    protected ?int $physicalRecordLength = null;

    public function __construct(
        protected string $line,
        ?int $physicalRecordLength = null
    ) {
        $this->setPhysicalRecordLength($physicalRecordLength);
        $this->endOfLine = strlen($this->line);
    }

    public function getPhysicalRecordLength(): ?int
    {
        return $this->physicalRecordLength;
    }

    public function setPhysicalRecordLength(?int $physicalRecordLength): self
    {
        // NOTE: if current physical record length is null, and argument is
        //   null, then we don't care (this call is a no-op in that case).
        //
        // NOTE: This ensures our internal buffer state never gets into a
        //   strange place with respect to truncation (truncation of the line
        //   is a one-way operation, and all this logic ensures higher level
        //   logic can not attempt to violate this one-way-ness).
        if (!is_null($this->physicalRecordLength)) {
            throw new \Exception('The physical record length may be set only once.');
        } else if (!is_null($physicalRecordLength)) {
            $this->physicalRecordLength = $physicalRecordLength;
            $this->validatePhysicalRecordLength()->trimLine();
        }

        return $this;
    }

    protected function validatePhysicalRecordLength(): self
    {
        if (!is_null($this->physicalRecordLength)) {
            if (strlen($this->line) > $this->physicalRecordLength) {
                throw new \Exception('Input line length exceeds requested physical record length.');
            }
        }

        return $this;
    }

    protected function trimLine(): self
    {
        $this->line = rtrim($this->line);
        return $this;
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

        $this->textTaken = true;
        $value = '';

        if ($this->readCharAt($this->cursor) !== '/') {
            $value = $this->readTo($this->endOfLine + 1);
        }

        return $value;
    }

    public function continuedTextField(): string
    {
        // TODO(zmd): dedupe logic with ::textField() if possible
        // TODO(zmd): disallow reading past end of buffer (see ::textField())
        if ($this->isEndOfLine()) {
            throw new \Exception('Cannot access fields at the end of the buffer.');
        }

        $this->textTaken = true;
        return $this->readTo($this->endOfLine + 1);
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
