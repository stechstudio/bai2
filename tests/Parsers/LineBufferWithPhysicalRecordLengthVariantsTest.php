<?php

namespace STS\Bai2\Parsers;

use PHPUnit\Framework\TestCase;

final class LineBufferWithPhysicalRecordLengthVariantsTest extends TestCase
{

    public function threeEatableFieldsProducer(): array
    {
        return [
            ["foo,bar,baz/",         null],
            ["foo,bar,baz/   ",      null],
            ["foo,bar,baz/        ", null],
            [",bar,baz/",            null],
            [",bar,baz/      ",      null],
            [",bar,baz/           ", null],
            ["foo,,baz/",            null],
            ["foo,,baz/      ",      null],
            ["foo,,baz/           ", null],
            ["foo,bar,/",            null],
            ["foo,bar,/      ",      null],
            ["foo,bar,/           ", null],
            ["foo,,/",               null],
            ["foo,,/         ",      null],
            ["foo,,/              ", null],
            [",,/",                  null],
            [",,/            ",      null],
            [",,/                 ", null],
            ["foo,bar,baz/",           20],
            ["foo,bar,baz/   ",        20],
            ["foo,bar,baz/        ",   20],
            [",bar,baz/",              20],
            [",bar,baz/      ",        20],
            [",bar,baz/           ",   20],
            ["foo,,baz/",              20],
            ["foo,,baz/      ",        20],
            ["foo,,baz/           ",   20],
            ["foo,bar,/",              20],
            ["foo,bar,/      ",        20],
            ["foo,bar,/           ",   20],
            ["foo,,/",                 20],
            ["foo,,/         ",        20],
            ["foo,,/              ",   20],
            [",,/",                    20],
            [",,/            ",        20],
            [",,/                 ",   20],
        ];
    }

    protected function withBuffer(array $bufferArgs, callable $callable): void {
        if (count($bufferArgs) == 2) {
            $callable(new LineBuffer(...$bufferArgs));
        } else {
            [$line, $length, $expected] = $bufferArgs;
            $buffer = new LineBuffer($line, $length);
            $callable($buffer, $expected);
        }
    }

    /**
     * @dataProvider threeEatableFieldsProducer
     */
    public function testRecognizesIsEndOfLineAfterEatingFields(...$bufferArgs): void
    {
        $this->withBuffer($bufferArgs, function ($buffer) {
            $this->assertFalse($buffer->isEndOfLine());
            $buffer->eat()->eat()->eat();
            $this->assertTrue($buffer->isEndOfLine());
        });
    }

    /**
     * @testWith ["foo,bar,baz/",         null]
     *           ["foo,bar,baz/   ",      null]
     *           ["foo,bar,baz/        ", null]
     *           ["foo,bar,baz/",           20]
     *           ["foo,bar,baz/   ",        20]
     *           ["foo,bar,baz/        ",   20]
     */
    public function testCanAccessFields(...$bufferArgs): void
    {
        $this->withBuffer($bufferArgs, function ($buffer) {
            $this->assertEquals('foo', $buffer->field());
            $this->assertEquals('bar', $buffer->eat()->field());
            $this->assertEquals('baz', $buffer->eat()->field());
        });
    }

    /**
     * @testWith ["foo,,baz/",            null]
     *           ["foo,,baz/      ",      null]
     *           ["foo,,baz/           ", null]
     *           ["foo,,baz/",              20]
     *           ["foo,,baz/      ",        20]
     *           ["foo,,baz/           ",   20]
     */
    public function testCanAccessDefaultedField(...$bufferArgs): void
    {
        $this->withBuffer($bufferArgs, function ($buffer) {
            $buffer->eat();
            $this->assertEquals('', $buffer->field());
        });
    }

    /**
     * @testWith ["foo,bar,baz quux",     null, "baz quux"]
     *           ["foo,bar,baz quux  ",   null, "baz quux  "]
     *           ["foo,bar,baz quux    ", null, "baz quux    "]
     *           ["foo,bar,baz quux",       20, "baz quux"]
     *           ["foo,bar,baz quux  ",     20, "baz quux"]
     *           ["foo,bar,baz quux    ",   20, "baz quux"]
     */
    public function testCanAccessTextField(...$bufferArgs): void {
        $this->withBuffer($bufferArgs, function ($buffer, $expected) {
            $buffer->eat()->eat();
            $this->assertEquals($expected, $buffer->textField());
        });
    }

    /**
     * @testWith ["foo,bar,baz/",         null, "bar,baz/"]
     *           ["foo,bar,baz/   ",      null, "bar,baz/   "]
     *           ["foo,bar,baz/        ", null, "bar,baz/        "]
     *           ["foo,,/",               null, ",/"]
     *           ["foo,,/         ",      null, ",/         "]
     *           ["foo,,/              ", null, ",/              "]
     *           ["foo,bar,baz/",           20, "bar,baz/"]
     *           ["foo,bar,baz/   ",        20, "bar,baz/"]
     *           ["foo,bar,baz/        ",   20, "bar,baz/"]
     *           ["foo,,/",                 20, ",/"]
     *           ["foo,,/         ",        20, ",/"]
     *           ["foo,,/              ",   20, ",/"]
     */
    public function testCanAccessTextFieldWithCommasAndSlashes(...$bufferArgs): void
    {
        $this->withBuffer($bufferArgs, function ($buffer, $expected) {
            $buffer->eat();
            $this->assertEquals($expected, $buffer->textField());
        });
    }

    /**
     * @testWith ["foo,bar,/",         null]
     *           ["foo,bar,/   ",      null]
     *           ["foo,bar,/        ", null]
     *           ["foo,bar,/",           20]
     *           ["foo,bar,/   ",        20]
     *           ["foo,bar,/        ",   20]
     */
    public function testCanAccessDefaultedTextField(...$bufferArgs): void
    {
        $this->withBuffer($bufferArgs, function ($buffer) {
            $buffer->eat()->eat();
            $this->assertEquals('', $buffer->textField());
        });
    }

    /**
     * @testWith ["foo,bar,baz/",                 null]
     *           ["foo,bar,baz/\t\t\t\t",         null]
     *           ["foo,bar,baz/\t\t\t\t\t\t\t\t", null]
     *           ["foo,bar,baz/blah",             null]
     *           ["foo,bar,baz/blahblah",         null]
     *           ["foo,bar,baz/",                   20]
     *           ["foo,bar,baz/\t\t\t\t",           20]
     *           ["foo,bar,baz/\t\t\t\t\t\t\t\t",   20]
     *           ["foo,bar,baz/blah",               20]
     *           ["foo,bar,baz/blahblah",           20]
     */
    public function testFieldAccessHandlesOtherFormsOfPadding(...$bufferArgs): void
    {
        $this->withBuffer($bufferArgs, function ($buffer) {
            $buffer->eat()->eat();
            $this->assertEquals('baz', $buffer->field());

            $buffer->eat();
            $this->assertTrue($buffer->isEndOfLine());
        });
    }

    /**
     * @testWith ["foo,bar,baz/",                 null, "bar,baz/"]
     *           ["foo,bar,baz/\t\t\t\t",         null, "bar,baz/\t\t\t\t"]
     *           ["foo,bar,baz/\t\t\t\t\t\t\t\t", null, "bar,baz/\t\t\t\t\t\t\t\t"]
     *           ["foo,bar,baz/blah",             null, "bar,baz/blah"]
     *           ["foo,bar,baz/blahblah",         null, "bar,baz/blahblah"]
     *           ["foo,bar,baz/",                   20, "bar,baz/"]
     *           ["foo,bar,baz/\t\t\t\t",           20, "bar,baz/"]
     *           ["foo,bar,baz/\t\t\t\t\t\t\t\t",   20, "bar,baz/"]
     *           ["foo,bar,baz/blah",               20, "bar,baz/blah"]
     *           ["foo,bar,baz/blahblah",           20, "bar,baz/blahblah"]
     */
    public function testTextFieldAccessHandlesOtherFormsOfPadding(...$bufferArgs): void
    {
        $this->withBuffer($bufferArgs, function ($buffer, $expected) {
            $buffer->eat();
            $this->assertEquals($expected, $buffer->textField());

            $buffer->eat();
            $this->assertTrue($buffer->isEndOfLine());
        });
    }

    /**
     * @dataProvider threeEatableFieldsProducer
     */
    public function testThrowsWhenEatingPastEndOfInput(...$bufferArgs): void
    {
        $this->withBuffer($bufferArgs, function ($buffer) {
            $buffer->eat()->eat()->eat();

            $this->expectException(\Exception::class);
            $this->expectExceptionMessage('Cannot advance beyond the end of the buffer.');
            $buffer->eat();
        });
    }

    /**
     * @dataProvider threeEatableFieldsProducer
     */
    public function testThrowsWhenAccessingFieldPastEndOfInput(...$bufferArgs): void
    {
        $this->withBuffer($bufferArgs, function ($buffer) {
            $buffer->eat()->eat()->eat();

            $this->expectException(\Exception::class);
            $this->expectExceptionMessage('Cannot access fields at the end of the buffer.');
            $buffer->field();
        });
    }

    /**
     * @dataProvider threeEatableFieldsProducer
     */
    public function testThrowsWhenAccessingTextFieldPastEndOfInput(...$bufferArgs): void
    {
        $this->withBuffer($bufferArgs, function ($buffer) {
            $buffer->eat()->eat()->eat();

            $this->expectException(\Exception::class);
            $this->expectExceptionMessage('Cannot access fields at the end of the buffer.');
            $buffer->textField();
        });

        $this->withBuffer($bufferArgs, function ($buffer) {
            $buffer->eat();
            $buffer->textField();
            $buffer->eat();

            $this->expectException(\Exception::class);
            $this->expectExceptionMessage('Cannot access fields at the end of the buffer.');
            $buffer->textField();
        });
    }

}
