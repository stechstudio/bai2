<?php

namespace STS\Bai2\Parsers;

class MultilineParser
{

    protected LineParser $currentLine;

    protected array $lines = [];

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
        $text = $this->currentOrNextLine()->shiftText();

        while ($this->currentOrNextLine()->hasMore()) {
            $text .= $this->currentOrNextLine()->shiftContinuedText();
        }

        return $text;
    }

    // TODO(zmd): would it be bad to call ::continue() after calling
    //   ::shiftText()?
    public function continue(string $continuationLine): self
    {
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
