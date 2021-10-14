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

    // TODO(zmd): it would be bad for the user to call ::continue() on a line
    //   which is not a continuation record. Also bad to call ::continue()
    //   after calling ::shiftText()
    public function continue(string $continuationLine): self
    {
        $lineParser = new LineParser($continuationLine);

        // immediately discard the '88' record type field
        $lineParser->shift();

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
