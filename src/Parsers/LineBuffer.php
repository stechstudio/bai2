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
        $this->assertNotEndOfLine();

        return $this->readTo($this->findFieldEnd());
    }

    public function textField(): string
    {
        $this->assertNotEndOfLine();

        $this->textTaken = true;
        $value = '';

        if ($this->readCharAt($this->cursor) !== '/') {
            $value = $this->readTo($this->endOfLine + 1);
        }

        return $value;
    }

    public function continuedTextField(): string
    {
        $this->assertNotEndOfLine();

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

    protected function seek(string ...$needles): ?int
    {
        $found = preg_match(
            $this->regexNeedle($needles),
            $this->line,
            $matches,
            PREG_OFFSET_CAPTURE, $this->cursor
        );

        if ($found) {
            return $matches[0][1];
        } else {
            return null;
        }

        return $found;
    }

    protected function regexNeedle(array $needles): string
    {
        $quoted = array_map(
            fn ($needle) => preg_quote($needle),
            $needles
        );

        $needle = '(' . implode('|', $quoted) . ')';

        return $needle;
    }

    protected function readTo(int $endIndex = null): string
    {
        $offset = $endIndex - $this->cursor;
        return substr($this->line, $this->cursor, $offset);
    }

    protected function findFieldEnd(): int
    {
        $end = $this->seek(',', '/');

        if (is_null($end)) {
            throw new \Exception('Cannot access last (non-text) field on unterminated input line.');
        }

        return $end;
    }

    protected function assertNotEndOfLine(): void
    {
        if ($this->isEndOfLine()) {
            throw new \Exception('Cannot access fields at the end of the buffer.');
        }
    }

}
