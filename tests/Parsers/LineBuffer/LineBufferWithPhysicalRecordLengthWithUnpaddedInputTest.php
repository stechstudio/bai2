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

    public function testCanAccessDefaultedField(): void
    {
        $this->buffer = new LineBuffer('foo,,baz/', 80);
        $this->buffer->eat();
        $this->assertEquals('', $this->buffer->field());
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

    public function testCanAccessDefaultedTextField(): void
    {
        $this->buffer = new LineBuffer('foo,bar,/', 80);
        $this->buffer->eat()->eat();
        $this->assertEquals('', $this->buffer->textField());
    }

    public function testThrowsWhenEatingPastEndOfInput(): void
    {
        $this->buffer->eat()->eat()->eat();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot advance beyond the end of the buffer.');
        $this->buffer->eat();
    }

    public function testThrowsWhenAccessingFieldPastEndOfInput(): void
    {
        $this->buffer->eat()->eat()->eat();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot access fields at the end of the buffer.');
        $this->buffer->field();
    }

    public function testThrowsWhenAccessingTextFieldPastEndOfInput(): void
    {
        $this->buffer->eat()->eat()->eat();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot access fields at the end of the buffer.');
        $this->buffer->textField();
    }

}
