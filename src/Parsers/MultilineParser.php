<?php

namespace STS\Bai2\Parsers;

class MultilineParser
{

    protected LineParser $currentLine;

    protected array $lines = [];

    protected bool $textTaken = false;

    public function __construct(string $firstLine)
    {
        $this->currentLine = new LineParser($firstLine);
    }

    public function peek(): ?string
    {
        return $this->currentOrNextLine()->peek();
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
            throw new \Exception('Cannot call ::continue() after reading the text field.');
        }

        $lineParser = new LineParser($continuationLine);

        // immediately check for then discard the '88' record type field
        if ($lineParser->shift() !== '88') {
            throw new \Exception('Cannot call ::continue() on non-continuation input.');
        }

        $this->lines[] = $lineParser;
        return $this;
    }

    protected function currentOrNextLine(): LineParser
    {
        if ($this->currentLine->isEndOfLine()) {
            $this->nextLine();
        }

        return $this->currentLine;
    }

    protected function nextLine(): void
    {
        if ($nextLine = array_shift($this->lines)) {
            $this->currentLine = $nextLine;
        }
    }

}
