<?php

namespace STS\Bai2\Parsers;

use PHPUnit\Framework\TestCase;

use STS\Bai2\Exceptions\InvalidTypeException;

final class FieldParserTest extends TestCase
{

    public function testString(): void
    {
        $parser = new FieldParser('foo', 'Foo Bar');
        $this->assertEquals('foo', $parser->string());
    }

    public function testStringValidatesPresence(): void
    {
        $parser = new FieldParser('', 'Foo Bar');

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Foo Bar" cannot be omitted.');
        $parser->string();
    }

    public function testStringWithDefault(): void
    {
        $parser = new FieldParser('foo', 'Foo Bar');
        $this->assertEquals('foo', $parser->string(default: 'bar'));
        $this->assertEquals('foo', $parser->string(default: null));
    }

    public function testStringWithDefaultFallsBackToDefault(): void
    {
        $parser = new FieldParser('', 'Foo Bar');
        $this->assertEquals('bar', $parser->string(default: 'bar'));
        $this->assertNull($parser->string(default: null));
    }

    public function testInt(): void
    {
        $parser = new FieldParser('101', 'Foo Bar');
        $this->assertEquals(101, $parser->int());
    }

    /**
     * @testWith ["01", 1]
     *           ["001", 1]
     *           ["00100", 100]
     */
    public function testIntWithLeadingZeros(string $value, int $expected): void
    {
        $parser = new FieldParser($value, 'Foo Bar');
        $this->assertEquals($expected, $parser->int());
    }

    public function testIntValidatesPresence(): void
    {
        $parser = new FieldParser('', 'Foo Bar');

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Foo Bar" cannot be omitted.');
        $parser->int();
    }

    public function testIntWithDefault(): void
    {
        $parser = new FieldParser(100, 'Foo Bar');
        $this->assertEquals(100, $parser->int(default: 200));
        $this->assertEquals(100, $parser->int(default: null));
    }

    public function testIntWithDefaultFallsBackToDefault(): void
    {
        $parser = new FieldParser('', 'Foo Bar');
        $this->assertEquals(200, $parser->int(default: 200));
        $this->assertNull($parser->int(default: null));
    }

}
