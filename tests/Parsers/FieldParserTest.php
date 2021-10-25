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

    public function testStringWithSatisfiedIsConstraint(): void
    {
        $parser = new FieldParser('apples', 'Fruit Basket');
        $parser->is('apples', 'must be apples yo');

        $this->assertEquals('apples', $parser->string());
    }

    public function testStringWithViolatedIsConstraint(): void
    {
        $parser = new FieldParser('oranges', 'Fruit Basket');
        $parser->is('apples', 'must be apples yo');

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Fruit Basket" must be apples yo.');
        $parser->string();
    }

    public function testIntWithSatisfiedIsConstraint(): void
    {
        $parser = new FieldParser('42', 'Meaning of Life');
        $parser->is('42', 'must have meaning yo');

        $this->assertEquals(42, $parser->int());
    }

    public function testIntWithViolatedIsConstraint(): void
    {
        $parser = new FieldParser('1337', 'Meaning of Life');
        $parser->is('42', 'must have meaning yo');

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Meaning of Life" must have meaning yo.');
        $parser->int();
    }

    public function testIsHasLowerPrescedenceThanImplicitRequiredConstraint(): void
    {
        $parser = new FieldParser('', 'Foo Bar');
        $parser->is('42', 'must have meaning yo');

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Foo Bar" cannot be omitted.');
        $parser->string();
    }

    // TODO(zmd): ::is() adjusts exception message if field defaulted (not required)

    /* TODO(zmd): ::is() is fluent
    public function testFluentIsConstraint(): void
    {
        $this->assertEquals(
            'apples',
            new FieldParser('apples', 'Fruit Basket')
                ->is('apples', 'must be apples yo')
                ->string()
        );
    }
    */

    // ---

    // TODO(zmd): ::match() is lower prescendence than implicit required constraint

    // TODO(zmd): ::match() adjusts exception message if field defaulted (not required)

    // TODO(zmd): ::match() is fluent

    // ---

    // TODO(zmd): ::is() throws if an is-constraint is already set

    // TODO(zmd): ::is() throws if a match-constraint is already set

    // TODO(zmd): ::match() throws if a match-constraint is already set

    // TODO(zmd): ::match() throws if an is-constraint is already set

}
