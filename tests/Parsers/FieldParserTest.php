<?php

namespace STS\Bai2\Parsers;

use PHPUnit\Framework\TestCase;

use STS\Bai2\Exceptions\InvalidTypeException;

final class FieldParserTest extends TestCase
{

    // -- cast/parse ------------------------------------------------------------

    public function testString(): void
    {
        $parser = new FieldParser('foo', 'Foo Bar');
        $this->assertEquals('foo', $parser->string());
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

    public function testStringValidatesPresence(): void
    {
        $parser = new FieldParser('', 'Foo Bar');

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Foo Bar" cannot be omitted.');
        $parser->string();
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

    public function testIntValidatesPresence(): void
    {
        $parser = new FieldParser('', 'Foo Bar');

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Foo Bar" cannot be omitted.');
        $parser->int();
    }

    // -- is constraint --------------------------------------------------------

    public function testIsConstraintSatisfied(): void
    {
        $parser = new FieldParser('apples', 'Fruit Basket');
        $parser->is('apples', 'must be apples yo');

        $this->assertEquals('apples', $parser->string());
    }

    public function testIsConstraintViolated(): void
    {
        $parser = new FieldParser('oranges', 'Fruit Basket');
        $parser->is('apples', 'must be apples yo');

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Fruit Basket" must be apples yo.');
        $parser->string();
    }

    public function testIsConstraintHasLowerPrecedenceThanRequiredConstraint(): void
    {
        $parser = new FieldParser('', 'Foo Bar');
        $parser->is('42', 'must have meaning yo');

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Foo Bar" cannot be omitted.');
        $parser->string();
    }

    public function testIsConstraintMessageAdjustsWhenFieldOptional(): void
    {
        $parser = new FieldParser('1337', 'Meaning of Life');
        $parser->is('42', 'must have meaning yo');

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Meaning of Life", if provided, must have meaning yo.');
        $parser->string(default: null);
    }

    public function testFluentIsConstraint(): void
    {
        $parser = new FieldParser('apples', 'Fruit Basket');
        $this->assertEquals(
            'apples',
            $parser
                ->is('apples', 'must be apples yo')
                ->string()
        );
    }

    // -- match constraint -----------------------------------------------------

    /**
     * @testWith ["apples"]
     *           ["applications"]
     */
    public function testMatchConstraintSatisfied(string $input): void
    {
        $parser = new FieldParser($input, 'Appetizers');
        $parser->match('/^app/', 'must be app');
        $this->assertEquals($input, $parser->string());
    }

    /**
     * @testWith ["blueberries"]
     *           ["integrated software"]
     */
    public function testMatchConstraintViolated(string $input): void
    {
        $parser = new FieldParser($input, 'Appetizers');
        $parser->match('/^app/', 'must be app');

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Appetizers" must be app.');
        $parser->string();
    }

    public function testMatchConstraintHasLowerPrecedenceThanRequiredConstraint(): void
    {
        $parser = new FieldParser('', 'Appetizers');
        $parser->match('/^app/', 'must be app');

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Appetizers" cannot be omitted.');
        $parser->string();
    }

    public function testMatchConstraintMessageAdjustsWhenFieldOptional(): void
    {
        $parser = new FieldParser('barbecue', 'Appetizers');
        $parser->match('/^app/', 'must be app');

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Appetizers", if provided, must be app.');
        $parser->string(default: 'appliance');
    }

    public function testFluentMatchConstraint(): void
    {
        $parser = new FieldParser('apples', 'Appetizers');
        $this->assertEquals(
            'apples',
            $parser
                ->match('/^app/', 'must be app')
                ->string()
        );
    }

}
