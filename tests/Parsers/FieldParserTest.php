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

    public function testViolatedIsConstraintMessageAdjustsInViewOfRequiredConstraint(): void
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

    /**
     * @testWith ["apples"]
     *           ["applications"]
     */
    public function testStringWithSatisfiedMatchConstraint(string $input): void
    {
        $parser = new FieldParser($input, 'Appetizers');
        $parser->match('/^app/', 'must be app');
        $this->assertEquals($input, $parser->string());
    }

    /**
     * @testWith ["blueberries"]
     *           ["integrated software"]
     */
    public function testStringWithViolatedMatchConstraint(string $input): void
    {
        $parser = new FieldParser($input, 'Appetizers');
        $parser->match('/^app/', 'must be app');

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Appetizers" must be app.');
        $parser->string();
    }

    /**
     * @testWith ["42300", 42300]
     *           ["421337", 421337]
     */
    public function testIntWithSatisfiedMatchConstraint(string $input, int $expected): void
    {
        $parser = new FieldParser($input, 'Meaningful Number');
        $parser->match('/^42/', 'must begin with meaning');
        $this->assertEquals($expected, $parser->int());
    }

    /**
     * @testWith ["300"]
     *           ["1337"]
     */
    public function testIntWithViolatedMatchConstraint(string $input): void
    {
        $parser = new FieldParser($input, 'Meaningful Number');
        $parser->match('/^42/', 'must begin with meaning');

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Meaningful Number" must begin with meaning.');
        $parser->int();
    }

    public function testViolatedMatchConstraintMessageAdjustsInViewOfRequiredConstraint(): void
    {
        $parser = new FieldParser('barbecue', 'Appetizers');
        $parser->match('/^app/', 'must be app');

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Appetizers", if provided, must be app.');
        $parser->string(default: 'appliance');
    }

    // TODO(zmd): ::match() is lower prescendence than implicit required constraint

    // TODO(zmd): ::match() is fluent

}
