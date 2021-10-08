<?php

namespace STS\Bai2\Parsers;

use PHPUnit\Framework\TestCase;

final class LineBufferTest extends TestCase
{

    public function testStartsOnFirstField(): void
    {
        $buffer = new LineBuffer('foo,bar,baz/');
        $field = $buffer->field();
        $this->assertEquals('foo', $field);
    }

    public function testCanGetCurrentFieldMultipleTime(): void
    {
        $buffer = new LineBuffer('foo,bar,baz/');

        $field = $buffer->field();
        $this->assertEquals('foo', $field);

        $field = $buffer->field();
        $this->assertEquals('foo', $field);

        $field = $buffer->field();
        $this->assertEquals('foo', $field);
    }

    public function testNextToAdvanceToNextField(): void
    {
        $buffer = new LineBuffer('foo,bar,baz/');
        $field = $buffer->eat()->field();
        $this->assertEquals('bar', $field);
    }

    public function testUseNextToAdvanceToThirdField(): void
    {
        $buffer = new LineBuffer('foo,bar,baz/');
        $field = $buffer->eat()->eat()->field();
        $this->assertEquals('baz', $field);
    }

    public function testCanGetTextField(): void
    {
        $buffer = new LineBuffer('foo,bar,baz/');
        $field = $buffer->eat()->textField();
        $this->assertEquals('bar,baz/', $field);
    }

    public function testCanGetTextFieldMultipleTimes(): void
    {
        $buffer = new LineBuffer('foo,bar,baz/');
        $buffer->eat();

        $field = $buffer->textField();
        $this->assertEquals('bar,baz/', $field);

        $field = $buffer->textField();
        $this->assertEquals('bar,baz/', $field);

        $field = $buffer->textField();
        $this->assertEquals('bar,baz/', $field);
    }

    public function testGetDefaultedField(): void
    {
        $buffer = new LineBuffer('foo,,baz/');
        $field = $buffer->eat()->field();
        $this->assertEquals('', $field);
    }

    public function testGetDefaultedLastField(): void
    {
        $buffer = new LineBuffer('foo,bar,/');
        $field = $buffer->eat()->eat()->field();
        $this->assertEquals('', $field);
    }

    public function testGetDefaultedTextField(): void
    {
        $buffer = new LineBuffer('foo,bar,/');
        $field = $buffer->eat()->eat()->textField();
        $this->assertEquals('', $field);
    }

    public function testIsEndOfLineReturnsFalseWhenNotStarted(): void
    {
        $buffer = new LineBuffer('foo,bar,baz/');
        $this->assertFalse($buffer->isEndOfLine());
    }

    public function testIsEndOfLineReturnsFalseWhenPartWayThrough(): void
    {
        $buffer = new LineBuffer('foo,bar,baz/');
        $buffer->eat();
        $this->assertFalse($buffer->isEndOfLine());
    }

    public function testIsEndOfLineReturnsFalseWhenOnLastField(): void
    {
        $buffer = new LineBuffer('foo,bar,baz/');
        $buffer->eat()->eat();
        $this->assertFalse($buffer->isEndOfLine());
    }

    public function testIsEndOfLineReturnsTrueWhenAtEndOfBuffer(): void
    {
        $buffer = new LineBuffer('foo,bar,baz/');
        $buffer->eat()->eat()->eat();
        $this->assertTrue($buffer->isEndOfLine());
    }

    public function testIsEndOfLineReturnsTrueAfterReadingTextFieldAndAdvancing(): void
    {
        $buffer = new LineBuffer('foo,bar,baz/');
        $buffer->eat()->textField();
        $buffer->eat();
        $this->assertTrue($buffer->isEndOfLine());
    }

    public function testIsEndOfLineReturnsTrueAfterReadingTextFieldAsFirstFieldAndAdvancing(): void
    {
        $buffer = new LineBuffer('foo,bar,baz/');
        $buffer->textField();
        $buffer->eat();
        $this->assertTrue($buffer->isEndOfLine());
    }

    public function testIsEndOfLineReturnsTrueAfterReadingDefaultedTextFieldAndAdvancing(): void
    {
        $buffer = new LineBuffer('foo,bar,/');
        $field = $buffer->eat()->eat()->textField();
        $buffer->eat();
        $this->assertTrue($buffer->isEndOfLine());
    }

    public function testCanAccessTextFieldAtEndWithoutRespectToEndingSlash(): void
    {
        $buffer = new LineBuffer('foo,bar,baz quux');
        $field = $buffer->eat()->eat()->textField();
        $this->assertEquals('baz quux', $field);
    }

    public function testThrowsExceptionWhenAccessingNormalFieldAtEndOfUnterminatedLine(): void
    {
        $buffer = new LineBuffer('foo,bar,baz quux');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot access last (non-text) field on unterminated input line.');
        $field = $buffer->eat()->eat()->field();
    }

    public function testThrowsExceptoinWhenAccessingNormalFieldAtEndOfUnterminedSingleFieldLine(): void
    {
        $buffer = new LineBuffer('foo');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot access last (non-text) field on unterminated input line.');
        $field = $buffer->field();
    }

    public function testCanAccessTextFieldAtEndOfSingleFieldLineWithoutRespectToEndingSlash(): void
    {
        $buffer = new LineBuffer('foo');
        $this->assertEquals('foo', $buffer->textField());
    }

    public function testCanAccessNormalFieldAtEndOfSingleFieldLineIfProperlyTerminated(): void
    {
        $buffer = new LineBuffer('foo/');
        $this->assertEquals('foo', $buffer->field());
    }

    public function testThrowsExceptionWhenAdvancingBeyondTheEndOfTheBuffer(): void
    {
        $buffer = new LineBuffer('foo,bar,baz/');
        $buffer->eat()->eat()->eat();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot advance beyond the end of the buffer.');
        $buffer->eat();
    }

    public function testThrowsExceptionWhenTryingToReadNormalFieldOnceAtEndOfTheBuffer(): void
    {
        $buffer = new LineBuffer('foo,bar,baz/');
        $buffer->eat()->eat()->eat();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot access fields at the end of the buffer.');
        $buffer->field();
    }

    public function testThrowsExceptionWhenTryingToReadTextFieldOnceAtEndOfTheBuffer(): void
    {
        $buffer = new LineBuffer('foo,bar,baz/');
        $buffer->eat()->eat()->eat();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot access fields at the end of the buffer.');
        $buffer->textField();
    }

}
