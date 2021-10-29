<?php

namespace STS\Bai2\Parsers;

use STS\Bai2\Exceptions\InvalidUseException;

class MultilineParser
{

    protected int $currentLine = 0;

    protected array $lines = [];

    protected bool $textTaken = false;

    public function __construct(
        string $firstLine,
        protected ?int $physicalRecordLength = null
    ) {
        $this->lines[] = new LineParser($firstLine, $this->physicalRecordLength);
    }

    public function setPhysicalRecordLength(?int $physicalRecordLength): void
    {
        $this->physicalRecordLength = $physicalRecordLength;

        foreach ($this->lines as $line) {
            $line->setPhysicalRecordLength($this->physicalRecordLength);
        }
    }

    public function peek(): ?string
    {
        return $this->currentOrNextLine()->peek();
    }

    public function hasMore(): bool
    {
        return $this->currentOrNextLine()->hasMore();
    }

    public function drop(int $numToDrop): array
    {
        $slice = [];
        for (; $numToDrop; --$numToDrop) {
            $slice[] = $this->currentOrNextLine()->shift();
        }
        return $slice;
    }

    public function shift(): string
    {
        return $this->currentOrNextLine()->shift();
    }

    public function shiftText(): string
    {
        $this->textTaken = true;
        $text = $this->currentOrNextLine()->shiftText();

        while ($this->currentOrNextLine()->hasMore()) {
            $text .= $this->currentOrNextLine()->shiftContinuedText();
        }

        return $text;
    }

    public function continue(string $continuationLine): self
    {
        if ($this->textTaken) {
            throw new InvalidUseException('Cannot call ::continue() after reading the text field.');
        }

        $lineParser = new LineParser($continuationLine, $this->physicalRecordLength);

        // immediately check for then discard the '88' record type field
        if ($lineParser->shift() !== '88') {
            throw new InvalidUseException('Cannot call ::continue() on non-continuation input.');
        }

        $this->lines[] = $lineParser;
        return $this;
    }

    protected function currentOrNextLine(): LineParser
    {
        if ($this->currentLine()->isEndOfLine()) {
            $this->nextLine();
        }

        return $this->currentLine();
    }

    protected function currentLine(): LineParser
    {
        return $this->lines[$this->currentLine];
    }

    protected function nextLine(): void
    {
        $nextLine = $this->currentLine + 1;
        if (array_key_exists($nextLine, $this->lines)) {
            $this->currentLine = $nextLine;
        }
    }

}
