<?php

namespace STS\Bai2\Parsers;

use PHPUnit\Framework\TestCase;

final class LineBufferTest extends TestCase
{

    public function testNextToGetFirstField()
    {
        $buffer = new LineBuffer('foo,bar,baz/');
        $field = $buffer->next()->field();
        $this->assertEquals('foo', $field);
    }

    public function testNextToGetSecondField()
    {
        $buffer = new LineBuffer('foo,bar,baz/');
        $field = $buffer->next()->next()->field();
        $this->assertEquals('bar', $field);
    }

    public function testNextToGetThirdField()
    {
        $buffer = new LineBuffer('foo,bar,baz/');
        $field = $buffer->next()->next()->next()->field();
        $this->assertEquals('baz', $field);
    }

    public function testNextAndPrevToGetSecondField()
    {
        $buffer = new LineBuffer('foo,bar,baz/');
        $field = $buffer->next()->next()->next()->prev()->field();
        $this->assertEquals('bar', $field);
    }

    public function testTextField()
    {
        $buffer = new LineBuffer('foo,bar,baz/');
        $field = $buffer->next()->next()->textField();
        $this->assertEquals('bar,baz/', $field);
    }

    public function testGetDefaultedField()
    {
        $buffer = new LineBuffer('foo,,baz/');
        $field = $buffer->next()->next()->field();
        $this->assertEquals('', $field);
    }

    public function testGetDefaultedTextField()
    {
        $buffer = new LineBuffer('foo,bar,/');
        $field = $buffer->next()->next()->next()->textField();
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
        $buffer->next()->next();
        $this->assertFalse($buffer->isEndOfLine());
    }

    public function testIsEndOfLineWhenPartOnLastField()
    {
        $buffer = new LineBuffer('foo,bar,baz/');
        $buffer->next()->next()->next();
        $this->assertFalse($buffer->isEndOfLine());
    }

    public function testIsEndOfLineWhenAtEnd()
    {
        $buffer = new LineBuffer('foo,bar,baz/');
        $buffer->next()->next()->next()->next();
        $this->assertTrue($buffer->isEndOfLine());
    }

    public function testIsEndOfLineNextAfterReadingTextField()
    {
        $buffer = new LineBuffer('foo,bar,baz/');
        $buffer->next()->next()->next()->textField();
        $buffer->next();
        $this->assertTrue($buffer->isEndOfLine());
    }


    // TODO(zmd): raises exception when dealing with normal field at end of
    //   un-terminated string

}
