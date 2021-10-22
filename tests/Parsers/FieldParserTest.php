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

}
