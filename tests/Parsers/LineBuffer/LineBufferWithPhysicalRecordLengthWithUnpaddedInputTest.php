<?php

namespace STS\Bai2\Parsers;

use PHPUnit\Framework\TestCase;

final class LineBufferWithPhysicalRecordLengthWithUnpaddedInputTest extends TestCase
{

    protected LineBuffer $buffer;

    protected array $lines = [
        'terminated' => 'foo,bar,baz/',
        'unterminated' => 'foo,bar,baz quux',
        'defaulted-field' => 'foo,,baz/',
        'defaulted-text-field' => 'foo,bar,/',
    ];

    protected function buffer(): LineBuffer {
        return $this->setBuffer()->buffer;
    }

    protected function setBuffer(
        string $desiredBuffer = 'terminated',
        int $physicalRecordLength = 80
    ): self {
        $this->buffer ??= new LineBuffer(
            $this->lines[$desiredBuffer],
            $physicalRecordLength
        );
        return $this;
    }

    public function testRecognizesIsEndOfLineAfterEatingFields(): void
    {
        $this->assertFalse($this->buffer()->isEndOfLine());
        $this->buffer()->eat()->eat()->eat();
        $this->assertTrue($this->buffer()->isEndOfLine());
    }

    public function testCanAccessFields(): void
    {
        $this->assertEquals('foo', $this->buffer()->field());
        $this->assertEquals('bar', $this->buffer()->eat()->field());
        $this->assertEquals('baz', $this->buffer()->eat()->field());
    }

    public function testCanAccessDefaultedField(): void
    {
        $this->setBuffer('defaulted-field');
        $this->buffer()->eat();
        $this->assertEquals('', $this->buffer()->field());
    }

    /**
     * @testWith ["foo,bar,baz quux", null, "baz quux"]
     *           ["foo,bar,baz quux  ", null, "baz quux  "]
     *           ["foo,bar,baz quux    ", null, "baz quux    "]
     *           ["foo,bar,baz quux", 20, "baz quux"]
     *           ["foo,bar,baz quux  ", 20, "baz quux"]
     *           ["foo,bar,baz quux    ", 20, "baz quux"]
     */
    public function testCanAccessTextField(
        string $input,
        ?int   $length,
        string $expected
    ): void {
        $this->buffer = new LineBuffer($input, $length);
        $this->buffer()->eat()->eat();
        $this->assertEquals($expected, $this->buffer->textField());
    }

    /**
     * @testWith ["foo,bar,baz/", null, "bar,baz/"]
     *           ["foo,bar,baz/   ", null, "bar,baz/   "]
     *           ["foo,bar,baz/        ", null, "bar,baz/        "]
     *           ["foo,bar,baz/", 20, "bar,baz/"]
     *           ["foo,bar,baz/   ", 20, "bar,baz/"]
     *           ["foo,bar,baz/        ", 20, "bar,baz/"]
     */
    public function testCanAccessTextFieldWithCommasAndSlashes(
        string $input,
        ?int   $length,
        string $expected
    ): void {
        $this->buffer = new LineBuffer($input, $length);
        $this->buffer->eat();
        $this->assertEquals($expected, $this->buffer->textField());
    }

    public function testCanAccessDefaultedTextField(): void
    {
        $this->setBuffer('defaulted-text-field');
        $this->buffer->eat()->eat();
        $this->assertEquals('', $this->buffer->textField());
    }

    public function testThrowsWhenEatingPastEndOfInput(): void
    {
        $this->buffer()->eat()->eat()->eat();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot advance beyond the end of the buffer.');
        $this->buffer()->eat();
    }

    public function testThrowsWhenAccessingFieldPastEndOfInput(): void
    {
        $this->buffer()->eat()->eat()->eat();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot access fields at the end of the buffer.');
        $this->buffer()->field();
    }

    public function testThrowsWhenAccessingTextFieldPastEndOfInput(): void
    {
        $this->buffer()->eat()->eat()->eat();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot access fields at the end of the buffer.');
        $this->buffer()->textField();
    }

}
