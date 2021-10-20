<?php

namespace STS\Bai2\Parsers;

class LineParser
{

    protected LineBuffer $buffer;

    public function __construct(string $line, ?int $physicalRecordLength = null)
    {
        // TODO(zmd): ::__construct() !!=> LineBufferLengthError
        $this->buffer = new LineBuffer($line, $physicalRecordLength);
    }

    public function setPhysicalRecordLength(?int $physicalRecordLength): self
    {
        // TODO(zmd): ::setPhysicalRecordLength() !!=> LineBufferLengthError
        $this->buffer->setPhysicalRecordLength($physicalRecordLength);
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
