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

    public function testNextToGetSecondField()
    {
        $buffer = new LineBuffer('foo,bar,baz/');
        $field = $buffer->next()->field();
        $this->assertEquals('bar', $field);
    }

    public function testNextToGetThirdField()
    {
        $buffer = new LineBuffer('foo,bar,baz/');
        $field = $buffer->next()->next()->field();
        $this->assertEquals('baz', $field);
    }

    public function testNextAndPrevToGetSecondField()
    {
        $buffer = new LineBuffer('foo,bar,baz/');
        $field = $buffer->next()->next()->prev()->field();
        $this->assertEquals('bar', $field);
    }

    public function testTextField()
    {
        $buffer = new LineBuffer('foo,bar,baz/');
        $field = $buffer->next()->textField();
        $this->assertEquals('bar,baz/', $field);
    }

    public function testGetDefaultedField()
    {
        $buffer = new LineBuffer('foo,,baz/');
        $field = $buffer->next()->field();
        $this->assertEquals('', $field);
    }

    public function testGetDefaultedTextField()
    {
        $buffer = new LineBuffer('foo,bar,/');
        $field = $buffer->next()->next()->textField();
        $this->assertEquals('', $field);
    }

    public function testIsEndOfLineWhenNotStarted()
    {
        $buffer = new LineBuffer('foo,bar,baz/');
        $this->assertFalse($buffer->isEndOfLine());
    }

    public function testIsEndOfLineWhenPartWayThrough()
    {
        $buffer = new LineBuffer('foo,bar,baz/');
        $buffer->next();
        $this->assertFalse($buffer->isEndOfLine());
    }

    public function testIsEndOfLineWhenOnLastField()
    {
        $buffer = new LineBuffer('foo,bar,baz/');
        $buffer->next()->next();
        $this->assertFalse($buffer->isEndOfLine());
    }

    public function testIsEndOfLineWhenAtEnd()
    {
        $buffer = new LineBuffer('foo,bar,baz/');
        $buffer->next()->next()->next();
        $this->assertTrue($buffer->isEndOfLine());
    }

    public function testIsEndOfLineAfterReadingTextFieldAndAdvancing()
    {
        $buffer = new LineBuffer('foo,bar,baz/');
        $buffer->next()->textField();
        $buffer->next();
        $this->assertTrue($buffer->isEndOfLine());
    }

    public function testIsEndOfLineAfterReadingDefaultedTextFieldAndAdvancing()
    {
        $buffer = new LineBuffer('foo,bar,/');
        $field = $buffer->next()->next()->textField();
        $buffer->next();
        $this->assertTrue($buffer->isEndOfLine());
    }

    // TODO(zmd): raises exception when dealing with normal field at end of
    //   un-terminated string

}
