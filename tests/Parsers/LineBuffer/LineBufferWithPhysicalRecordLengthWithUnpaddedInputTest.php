<?php

namespace STS\Bai2\Parsers;

use PHPUnit\Framework\TestCase;

final class LineBufferWithPhysicalRecordLengthWithUnpaddedInputTest extends TestCase
{
    private LineBuffer $buffer;

    protected function setUp(): void
    {
        $this->buffer = new LineBuffer('foo,bar,baz/', 80);
    }

    public function testRecognizesIsEndOfLineAfterEatingFields(): void
    {
        $this->assertFalse($this->buffer->isEndOfLine());
        $this->buffer->eat()->eat()->eat();
        $this->assertTrue($this->buffer->isEndOfLine());
    }

    public function testCanAccessFields(): void
    {
        $this->assertEquals('foo', $this->buffer->field());
        $this->assertEquals('bar', $this->buffer->eat()->field());
        $this->assertEquals('baz', $this->buffer->eat()->field());
    }

    public function testCanAccessTextField(): void
    {
        $this->buffer = new LineBuffer('foo,bar,baz quux', 80);
        $this->buffer->eat()->eat();
        $this->assertEquals('baz quux', $this->buffer->textField());
    }

    public function testCanAccessTextFieldWithCommasAndSlashes(): void
    {
        $this->buffer->eat();
        $this->assertEquals('bar,baz/', $this->buffer->textField());
    }

    // TODO(zmd): defaulted field access unpadded input
    // TODO(zmd): defaulted text field access unpadded input

    // TODO(zmd): eat bounds checks unpadded input
    // TODO(zmd): field bounds checks unpadded input
    // TODO(zmd): text field bounds checks unpadded input

}
