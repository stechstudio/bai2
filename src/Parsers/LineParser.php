<?php

namespace STS\Bai2\Parsers;

use STS\Bai2\Exceptions\LineBufferLengthException;
use STS\Bai2\Exceptions\LineBufferReadException;
use STS\Bai2\Exceptions\LineParserInputException;

class LineParser
{

    protected LineBuffer $buffer;

    public function __construct(string $line, ?int $physicalRecordLength = null)
    {
        try {
            $this->buffer = new LineBuffer($line, $physicalRecordLength);
        } catch (LineBufferLengthException $e) {
            throw new LineParserInputException('Input line length exceeds requested physical record length.');
        }
    }

    public function setPhysicalRecordLength(?int $physicalRecordLength): self
    {
        try {
            $this->buffer->setPhysicalRecordLength($physicalRecordLength);
        } catch (LineBufferLengthException $e) {
            throw new LineParserInputException('Input line length exceeds requested physical record length.');
        }

        return $this;
    }

    public function peek(): string
    {
        // TODO(zmd): ::field() !!=> LineBufferReadException
        return $this->buffer->field();
    }

    public function drop(int $numToDrop): array
    {
        $slice = [];
        for (; $numToDrop; --$numToDrop) {
            $slice[] = $this->shift();
        }
        return $slice;
    }

    public function shift(): string
    {
        // TODO(zmd): ::field() !!=> LineBufferReadException
        $field = $this->buffer->field();
        // TODO(zmd): ::eat() !!=> LineBufferReadException
        $this->buffer->eat();
        return $field;
    }

    public function shiftText(): string
    {
        // TODO(zmd): ::textField() !!=> LineBufferReadException
        $field = $this->buffer->textField();
        // TODO(zmd): ::eat() !!=> LineBufferReadException
        $this->buffer->eat();
        return $field;
    }

    public function shiftContinuedText(): string
    {
        // TODO(zmd): ::continuedTextField() !!=> LineBufferReadException
        $field = $this->buffer->continuedTextField();
        // TODO(zmd): ::eat() !!=> LineBufferReadException
        $this->buffer->eat();
        return $field;
    }

    public function hasMore(): bool
    {
        return !$this->isEndOfLine();
    }

    public function isEndOfLine(): bool
    {
        return $this->buffer->isEndOfLine();
    }

}
