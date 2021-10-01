<?php

namespace STS\Bai2\Parsers;

class LineParserBuffer
{

    protected int $numFieldsYielded = 0;

    protected string $buffer;

    protected bool $valid = false;

    public function __construct(
        protected string $line,
        protected int $numFields = 0
    ) {
        $this->buffer = $line;
    }

    public function next(): ?string
    {
        if ($this->isEndOfLine()) {
            $this->valid = false;
            return null;
        } else {
            $this->valid = true;
            return $this->shift();
        }
    }

    public function valid(): bool
    {
        return $this->valid;
    }

    protected function isEndOfLine(): bool
    {
        if ($this->numFields) {
            return $this->numFieldsYielded >= $this->numFields;
        } else {
            return $this->buffer == '/';
        }
    }

    protected function shift(): string
    {
        $this->numFieldsYielded++;

        if ($this->isLastExpectedField()) {
            return $this->buffer;
        }

        [$field, $rest] = $this->bisect();
        $this->buffer = $rest;

        return $field;
    }

    protected function isLastExpectedField(): bool
    {
        if ($this->numFields) {
            return $this->numFieldsYielded == $this->numFields;
        }

        return false;
    }

    protected function bisect(): array
    {
        $field = '';
        $rest = '/';

        $exploded = explode(',', $this->buffer, 2);
        if (count($exploded) == 1) {
            $field = rtrim($exploded[0], '/');
        } else {
            $field = $exploded[0];
            $rest = $exploded[1];
        }

        return [$field, $rest];
    }

}
