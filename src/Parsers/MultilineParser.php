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
        return $this->currentLine()->peek();
    }

    public function drop(int $numToDrop): array
    {
        $slice = [];
        for (; $numToDrop; --$numToDrop) {
            $slice[] = $this->currentLine()->shift();
        }
        return $slice;
    }

    public function shift(): string
    {
        return $this->currentLine()->shift();
    }

    // TODO(zmd): scold any user who tries to call ::shiftText() more than once.
    public function shiftText(): string
    {
        $text = $this->currentLine()->shiftText();

        // TODO(zmd): this is as clear as mud :[
        while (!$this->currentLine()->hasMore()) {
            $text .= $this->currentLine()->shiftText();
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

    protected function currentLine(): LineParser
    {
        if ($this->isCurrentLineExhausted()) {
            $this->nextLine();
        }

        return $this->currentLine;
    }

    protected function isCurrentLineExhausted(): bool
    {
        return !$this->currentLine->hasMore();
    }

    protected function nextLine(): void
    {
        if ($nextLine = array_shift($this->lines)) {
            $this->currentLine = $nextLine;
        }
    }

}
