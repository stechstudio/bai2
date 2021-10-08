<?php

namespace STS\Bai2\Parsers;

use PHPUnit\Framework\TestCase;

final class LineBufferTest extends TestCase
{

    public function testStartsOnFirstField()
    {
        $buffer = new LineBuffer('foo,bar,baz/');
        $field = $buffer->field();
        $this->assertEquals('foo', $field);
    }

    public function testCanGetCurrentFieldMultipleTime()
    {
        $buffer = new LineBuffer('foo,bar,baz/');

        $field = $buffer->field();
        $this->assertEquals('foo', $field);

        $field = $buffer->field();
        $this->assertEquals('foo', $field);

        $field = $buffer->field();
        $this->assertEquals('foo', $field);
    }

    public function testNextToAdvanceToNextField()
    {
        $buffer = new LineBuffer('foo,bar,baz/');
        $field = $buffer->eat()->field();
        $this->assertEquals('bar', $field);
    }

    public function testUseNextToAdvanceToThirdField()
    {
        $buffer = new LineBuffer('foo,bar,baz/');
        $field = $buffer->eat()->eat()->field();
        $this->assertEquals('baz', $field);
    }

    public function testCanGetTextField()
    {
        $buffer = new LineBuffer('foo,bar,baz/');
        $field = $buffer->eat()->textField();
        $this->assertEquals('bar,baz/', $field);
    }

    public function testCanGetTextFieldMultipleTimes()
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

    public function testGetDefaultedField()
    {
        $buffer = new LineBuffer('foo,,baz/');
        $field = $buffer->eat()->field();
        $this->assertEquals('', $field);
    }

    public function testGetDefaultedLastField()
    {
        $buffer = new LineBuffer('foo,bar,/');
        $field = $buffer->eat()->eat()->field();
        $this->assertEquals('', $field);
    }

    public function testGetDefaultedTextField()
    {
        $buffer = new LineBuffer('foo,bar,/');
        $field = $buffer->eat()->eat()->textField();
        $this->assertEquals('', $field);
    }

    public function testIsEndOfLineReturnsFalseWhenNotStarted()
    {
        $buffer = new LineBuffer('foo,bar,baz/');
        $this->assertFalse($buffer->isEndOfLine());
    }

    public function testIsEndOfLineReturnsFalseWhenPartWayThrough()
    {
        $buffer = new LineBuffer('foo,bar,baz/');
        $buffer->eat();
        $this->assertFalse($buffer->isEndOfLine());
    }

    public function testIsEndOfLineReturnsFalseWhenOnLastField()
    {
        $buffer = new LineBuffer('foo,bar,baz/');
        $buffer->eat()->eat();
        $this->assertFalse($buffer->isEndOfLine());
    }

    public function testIsEndOfLineReturnsTrueWhenAtEndOfBuffer()
    {
        $buffer = new LineBuffer('foo,bar,baz/');
        $buffer->eat()->eat()->eat();
        $this->assertTrue($buffer->isEndOfLine());
    }

    public function testIsEndOfLineReturnsTrueAfterReadingTextFieldAndAdvancing()
    {
        $buffer = new LineBuffer('foo,bar,baz/');
        $buffer->eat()->textField();
        $buffer->eat();
        $this->assertTrue($buffer->isEndOfLine());
    }

    public function testIsEndOfLineReturnsTrueAfterReadingTextFieldAsFirstFieldAndAdvancing()
    {
        $buffer = new LineBuffer('foo,bar,baz/');
        $buffer->textField();
        $buffer->eat();
        $this->assertTrue($buffer->isEndOfLine());
    }

    public function testIsEndOfLineReturnsTrueAfterReadingDefaultedTextFieldAndAdvancing()
    {
        $buffer = new LineBuffer('foo,bar,/');
        $field = $buffer->eat()->eat()->textField();
        $buffer->eat();
        $this->assertTrue($buffer->isEndOfLine());
    }

    public function testCanAccessTextFieldAtEndWithoutRespectToEndingSlash()
    {
        $buffer = new LineBuffer('foo,bar,baz quux');
        $field = $buffer->eat()->eat()->textField();
        $this->assertEquals('baz quux', $field);
    }

    public function testThrowsExceptionWhenAccessingNormalFieldAtEndOfUnterminatedLine()
    {
        $buffer = new LineBuffer('foo,bar,baz quux');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot access last (non-text) field on unterminated input line.');
        $field = $buffer->eat()->eat()->field();
    }

    public function testThrowsExceptoinWhenAccessingNormalFieldAtEndOfUnterminedSingleFieldLine()
    {
        $buffer = new LineBuffer('foo');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot access last (non-text) field on unterminated input line.');
        $field = $buffer->field();
    }

    public function testCanAccessTextFieldAtEndOfSingleFieldLineWithoutRespectToEndingSlash()
    {
        $buffer = new LineBuffer('foo');
        $this->assertEquals('foo', $buffer->textField());
    }

    public function testCanAccessNormalFieldAtEndOfSingleFieldLineIfProperlyTerminated()
    {
        $buffer = new LineBuffer('foo/');
        $this->assertEquals('foo', $buffer->field());
    }

    public function testThrowsExceptionWhenAdvancingBeyondTheEndOfTheBuffer()
    {
        $buffer = new LineBuffer('foo,bar,baz/');
        $buffer->eat()->eat()->eat();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot access advance beyond the end of the buffer.');
        $buffer->eat();
    }

    public function testThrowsExceptionWhenTryingToReadNormalFieldOnceAtEndOfTheBuffer()
    {
        $buffer = new LineBuffer('foo,bar,baz/');
        $buffer->eat()->eat()->eat();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot access fields at the end of the buffer.');
        $buffer->field();
    }

    public function testThrowsExceptionWhenTryingToReadTextFieldOnceAtEndOfTheBuffer()
    {
        $buffer = new LineBuffer('foo,bar,baz/');
        $buffer->eat()->eat()->eat();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot access fields at the end of the buffer.');
        $buffer->textField();
    }

}
