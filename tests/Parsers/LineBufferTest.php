<?php

namespace STS\Bai2\Parsers;

use PHPUnit\Framework\TestCase;

final class LineBufferTest extends TestCase
{

    private LineBuffer $buffer;

    protected function setUp(): void
    {
        $this->buffer = new LineBuffer('foo,bar,baz/');
    }

    public function testStartsOnFirstField(): void
    {
        $field = $this->buffer->field();
        $this->assertEquals('foo', $field);
    }

    public function testCanGetCurrentFieldMultipleTime(): void
    {
        $field = $this->buffer->field();
        $this->assertEquals('foo', $field);

        $field = $this->buffer->field();
        $this->assertEquals('foo', $field);

        $field = $this->buffer->field();
        $this->assertEquals('foo', $field);
    }

    public function testNextToAdvanceToNextField(): void
    {
        $field = $this->buffer->eat()->field();
        $this->assertEquals('bar', $field);
    }

    public function testUseNextToAdvanceToThirdField(): void
    {
        $field = $this->buffer->eat()->eat()->field();
        $this->assertEquals('baz', $field);
    }

    public function testCanGetTextField(): void
    {
        $field = $this->buffer->eat()->textField();
        $this->assertEquals('bar,baz/', $field);
    }

    public function testCanGetTextFieldMultipleTimes(): void
    {
        $this->buffer->eat();

        $field = $this->buffer->textField();
        $this->assertEquals('bar,baz/', $field);

        $field = $this->buffer->textField();
        $this->assertEquals('bar,baz/', $field);

        $field = $this->buffer->textField();
        $this->assertEquals('bar,baz/', $field);
    }

    public function testGetDefaultedField(): void
    {
        $this->buffer = new LineBuffer('foo,,baz/');
        $field = $this->buffer->eat()->field();
        $this->assertEquals('', $field);
    }

    public function testGetDefaultedLastField(): void
    {
        $this->buffer = new LineBuffer('foo,bar,/');
        $field = $this->buffer->eat()->eat()->field();
        $this->assertEquals('', $field);
    }

    public function testGetDefaultedTextField(): void
    {
        $this->buffer = new LineBuffer('foo,bar,/');
        $field = $this->buffer->eat()->eat()->textField();
        $this->assertEquals('', $field);
    }

    public function testTextFieldPaddedLineEndingRetained(): void
    {
        $this->buffer = new LineBuffer('foo,bar,baz                   ');
        $field = $this->buffer->eat()->eat()->textField();
        $this->assertEquals('baz                   ', $field);
    }

    public function testIsEndOfLineReturnsFalseWhenNotStarted(): void
    {
        $this->assertFalse($this->buffer->isEndOfLine());
    }

    public function testIsEndOfLineReturnsFalseWhenPartWayThrough(): void
    {
        $this->buffer->eat();
        $this->assertFalse($this->buffer->isEndOfLine());
    }

    public function testIsEndOfLineReturnsFalseWhenOnLastField(): void
    {
        $this->buffer->eat()->eat();
        $this->assertFalse($this->buffer->isEndOfLine());
    }

    public function testIsEndOfLineReturnsTrueWhenAtEndOfBuffer(): void
    {
        $this->buffer->eat()->eat()->eat();
        $this->assertTrue($this->buffer->isEndOfLine());
    }

    public function testIsEndOfLineReturnsTrueAfterReadingTextFieldAndAdvancing(): void
    {
        $this->buffer->eat()->textField();
        $this->buffer->eat();
        $this->assertTrue($this->buffer->isEndOfLine());
    }

    public function testIsEndOfLineReturnsTrueAfterReadingTextFieldAsFirstFieldAndAdvancing(): void
    {
        $this->buffer->textField();
        $this->buffer->eat();
        $this->assertTrue($this->buffer->isEndOfLine());
    }

    public function testIsEndOfLineReturnsTrueAfterReadingDefaultedTextFieldAndAdvancing(): void
    {
        $this->buffer = new LineBuffer('foo,bar,/');
        $field = $this->buffer->eat()->eat()->textField();
        $this->buffer->eat();
        $this->assertTrue($this->buffer->isEndOfLine());
    }

    public function testCanAccessTextFieldAtEndWithoutRespectToEndingSlash(): void
    {
        $this->buffer = new LineBuffer('foo,bar,baz quux');
        $field = $this->buffer->eat()->eat()->textField();
        $this->assertEquals('baz quux', $field);
    }

    public function testThrowsExceptionWhenAccessingNormalFieldAtEndOfUnterminatedLine(): void
    {
        $this->buffer = new LineBuffer('foo,bar,baz quux');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot access last (non-text) field on unterminated input line.');
        $field = $this->buffer->eat()->eat()->field();
    }

    public function testThrowsExceptoinWhenAccessingNormalFieldAtEndOfUnterminedSingleFieldLine(): void
    {
        $this->buffer = new LineBuffer('foo');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot access last (non-text) field on unterminated input line.');
        $field = $this->buffer->field();
    }

    public function testCanAccessTextFieldAtEndOfSingleFieldLineWithoutRespectToEndingSlash(): void
    {
        $this->buffer = new LineBuffer('foo');
        $this->assertEquals('foo', $this->buffer->textField());
    }

    public function testCanAccessNormalFieldAtEndOfSingleFieldLineIfProperlyTerminated(): void
    {
        $this->buffer = new LineBuffer('foo/');
        $this->assertEquals('foo', $this->buffer->field());
    }

    public function testThrowsExceptionWhenAdvancingBeyondTheEndOfTheBuffer(): void
    {
        $this->buffer->eat()->eat()->eat();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot advance beyond the end of the buffer.');
        $this->buffer->eat();
    }

    public function testThrowsExceptionWhenTryingToReadNormalFieldOnceAtEndOfTheBuffer(): void
    {
        $this->buffer->eat()->eat()->eat();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot access fields at the end of the buffer.');
        $this->buffer->field();
    }

    public function testThrowsExceptionWhenTryingToReadTextFieldOnceAtEndOfTheBuffer(): void
    {
        $this->buffer->eat()->eat()->eat();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot access fields at the end of the buffer.');
        $this->buffer->textField();
    }

}
