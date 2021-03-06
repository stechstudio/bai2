<?php

declare(strict_types=1);

namespace STS\Bai2\Parsers;

use PHPUnit\Framework\TestCase;

use STS\Bai2\Exceptions\InvalidUseException;
use STS\Bai2\Exceptions\MalformedInputException;
use STS\Bai2\Exceptions\ParseException;

final class LineBufferTest extends TestCase
{

    private LineBuffer $buffer;

    protected function setUp(): void
    {
        $this->buffer = new LineBuffer('foo,bar,baz/');
    }

    public function longInputProducer(): array
    {
        return [
            ["foo,bar,baz,foo,bar,baz,foo,bar,baz,foo,bar,baz,foo,bar,baz,foo,bar,baz,foo,bar,baz/"],
            ["foo,bar,baz/                                                                        "],
            ["foo,bar,baz/                                                                     "],
        ];
    }

    public function testConstructWithoutPhysicalRecordLengthSpecified(): void
    {
        $this->assertNull($this->buffer->getPhysicalRecordLength());
    }

    public function testConstructWithPhysicalRecordLengthSpecified(): void
    {
        $this->buffer = new LineBuffer('foo,bar,baz/', 80);
        $this->assertEquals(80, $this->buffer->getPhysicalRecordLength());
    }

    public function testConstructThenSetPhysicalRecordLength(): void
    {
        $this->buffer->setPhysicalRecordLength(80);
        $this->assertEquals(80, $this->buffer->getPhysicalRecordLength());
    }

    public function testSettingPhysicalRecordLengthTruncatesTrailingWhitespaceFromTextField(): void
    {
        $this->buffer = new LineBuffer('foo,bar,baz/                                                                    ');
        $this->buffer->eat()->eat();

        $this->assertEquals(
            'baz/                                                                    ',
            $this->buffer->textField()
        );

        $this->buffer->setPhysicalRecordLength(80);
        $this->assertEquals(
            'baz/',
            $this->buffer->textField()
        );
    }

    public function testSettingPhysicalRecordLengthTruncatesTrailingWhitespaceFromContinuedTextField(): void
    {
        $this->buffer = new LineBuffer('foo,bar,baz/                                                                    ');
        $this->buffer->eat()->eat();

        $this->assertEquals(
            'baz/                                                                    ',
            $this->buffer->continuedTextField()
        );

        $this->buffer->setPhysicalRecordLength(80);
        $this->assertEquals(
            'baz/',
            $this->buffer->continuedTextField()
        );
    }

    /**
     * @dataProvider longInputProducer
     */
    public function testThrowsWhenContructWithLineLongerThanPhsyicalLength(string $input): void
    {
        $this->expectException(MalformedInputException::class);
        $this->expectExceptionMessage('Input line length exceeds requested physical record length.');
        $this->buffer = new LineBuffer($input, 80);
    }

    /**
     * @dataProvider longInputProducer
     */
    public function testThrowsWhenSetPhysicalRecordLengthExceedingOriginalLineLength(string $input): void
    {
        $this->buffer = new LineBuffer($input);

        $this->expectException(MalformedInputException::class);
        $this->expectExceptionMessage('Input line length exceeds requested physical record length.');
        $this->buffer->setPhysicalRecordLength(80);
    }

    public function testThrowsWhenTryingToSetPhysicalRecordLengthToNullAfterConstructionWithLength(): void
    {
        $this->buffer = new LineBuffer('foo,bar,baz/', 80);

        $this->expectException(InvalidUseException::class);
        $this->expectExceptionMessage('The physical record length may be set only once.');
        $this->buffer->setPhysicalRecordLength(null);
    }

    public function testThrowsWhenTryingToSetPhysicalRecordLengthToNonNullAfterConstructionWithLength(): void
    {
        $this->buffer = new LineBuffer('foo,bar,baz/', 80);

        $this->expectException(InvalidUseException::class);
        $this->expectExceptionMessage('The physical record length may be set only once.');
        $this->buffer->setPhysicalRecordLength(100);
    }

    public function testThrowsWhenTryingToSetPhysicalRecordLengthToNullAfterSetingToNonNull(): void
    {
        $this->buffer = new LineBuffer('foo,bar,baz/');
        $this->buffer->setPhysicalRecordLength(80);

        $this->expectException(InvalidUseException::class);
        $this->expectExceptionMessage('The physical record length may be set only once.');
        $this->buffer->setPhysicalRecordLength(null);
    }

    public function testThrowsWhenTryingToSetPhysicalRecordLengthToNonNullAfterSetingToNonNull(): void
    {
        $this->buffer = new LineBuffer('foo,bar,baz/');
        $this->buffer->setPhysicalRecordLength(80);

        $this->expectException(InvalidUseException::class);
        $this->expectExceptionMessage('The physical record length may be set only once.');
        $this->buffer->setPhysicalRecordLength(79);
    }

    public function testNoOpToSetPhysicalRecordLengthToNullWhenAlreadyNull(): void
    {
        $this->buffer = new LineBuffer('foo,bar,baz/');

        $this->assertNull($this->buffer->getPhysicalRecordLength());
        $this->buffer->setPhysicalRecordLength(null);
        $this->assertNull($this->buffer->getPhysicalRecordLength());
    }

    public function testStartsOnFirstField(): void
    {
        $field = $this->buffer->field();
        $this->assertEquals('foo', $field);
    }

    public function testCanGetCurrentFieldMultipleTime(): void
    {
        $field = $this->buffer->field();
        $this->assertEquals('foo', $field);

        $field = $this->buffer->field();
        $this->assertEquals('foo', $field);

        $field = $this->buffer->field();
        $this->assertEquals('foo', $field);
    }

    public function testNextToAdvanceToNextField(): void
    {
        $field = $this->buffer->eat()->field();
        $this->assertEquals('bar', $field);
    }

    public function testUseNextToAdvanceToThirdField(): void
    {
        $field = $this->buffer->eat()->eat()->field();
        $this->assertEquals('baz', $field);
    }

    public function testCanGetTextField(): void
    {
        $this->buffer = new LineBuffer('foo,bar,baz quux');
        $field = $this->buffer->eat()->eat()->textField();
        $this->assertEquals('baz quux', $field);
    }

    public function testCanGetTextFieldWithCommasAndSlashes(): void
    {
        $field = $this->buffer->eat()->textField();
        $this->assertEquals('bar,baz/', $field);
    }

    public function testCanGetTextFieldWhichStartsWithComma(): void
    {
        $this->buffer = new LineBuffer('foo,bar,,yes...this is stupid');
        $field = $this->buffer->eat()->eat()->textField();
        $this->assertEquals(',yes...this is stupid', $field);
    }

    public function testCanGetTextFieldMultipleTimes(): void
    {
        $this->buffer->eat();

        $field = $this->buffer->textField();
        $this->assertEquals('bar,baz/', $field);

        $field = $this->buffer->textField();
        $this->assertEquals('bar,baz/', $field);

        $field = $this->buffer->textField();
        $this->assertEquals('bar,baz/', $field);
    }

    public function testCanGetContinuedTextField(): void
    {
        $this->buffer = new LineBuffer('88,this is text field');
        $field = $this->buffer->eat()->continuedTextField();
        $this->assertEquals('this is text field', $field);
    }

    public function testCanGetContinuedTextFieldWithCommasAndSlashes(): void
    {
        $this->buffer = new LineBuffer(
            '88,this is text field with ,s and /s, ending also (for the lols) with a /'
        );

        $field = $this->buffer->eat()->continuedTextField();

        $this->assertEquals(
            'this is text field with ,s and /s, ending also (for the lols) with a /',
            $field
        );
    }

    public function testCanGetContinuedTextFieldWhichStartsWithComma(): void
    {
        $this->buffer = new LineBuffer('88,,this is text field which started with comma');
        $field = $this->buffer->eat()->continuedTextField();
        $this->assertEquals(',this is text field which started with comma', $field);
    }

    public function testCanGetContinuedTextFieldWhichStartsWithSlash(): void
    {
        // You totally can't start a text field with a slash, but when you're
        // processing a continuation of an existing text field, then this first
        // slash isn't really the beginning of a text field at all.
        $this->buffer = new LineBuffer(
            '88,/this is *CONTINUED* text field which started with slash'
        );

        $field = $this->buffer->eat()->continuedTextField();

        $this->assertEquals(
            '/this is *CONTINUED* text field which started with slash',
            $field
        );
    }

    public function testCanGetContinuedTextFieldWhichIsJustASlash(): void
    {
        $this->buffer = new LineBuffer('88,/');

        $field = $this->buffer->eat()->continuedTextField();
        $this->assertEquals('/', $field);
    }

    public function testCanGetContinuedTextFieldMultipleTimes(): void
    {
        $this->buffer = new LineBuffer('88,/,,/,//,,/,/because murphy/');
        $this->buffer->eat();

        $this->assertEquals(
            '/,,/,//,,/,/because murphy/',
            $this->buffer->continuedTextField()
        );

        $this->assertEquals(
            '/,,/,//,,/,/because murphy/',
            $this->buffer->continuedTextField()
        );

        $this->assertEquals(
            '/,,/,//,,/,/because murphy/',
            $this->buffer->continuedTextField()
        );
    }

    public function testGetDefaultedField(): void
    {
        $this->buffer = new LineBuffer('foo,,baz/');
        $field = $this->buffer->eat()->field();
        $this->assertEquals('', $field);
    }

    public function testGetDefaultedLastField(): void
    {
        $this->buffer = new LineBuffer('foo,bar,/');
        $field = $this->buffer->eat()->eat()->field();
        $this->assertEquals('', $field);
    }

    public function testGetLastFieldIgnoresTrailingWhitespace(): void
    {
        $this->buffer = new LineBuffer('foo,bar,baz/        ');
        $field = $this->buffer->eat()->eat()->field();
        $this->assertEquals('baz', $field);
    }

    public function testGetLastFieldIgnoresTrailingCharacters(): void
    {
        $this->buffer = new LineBuffer('foo,bar,baz/ignoreme');
        $field = $this->buffer->eat()->eat()->field();
        $this->assertEquals('baz', $field);
    }

    public function testGetDefaultedTextField(): void
    {
        $this->buffer = new LineBuffer('foo,bar,/');
        $field = $this->buffer->eat()->eat()->textField();
        $this->assertEquals('', $field);
    }

    public function testGetDefaultedTextFieldIgnoresTrailingWhitespace(): void
    {
        $this->buffer = new LineBuffer('foo,bar,/        ');
        $field = $this->buffer->eat()->eat()->textField();
        $this->assertEquals('', $field);
    }

    public function testGetDefaultedTextFieldIgnoresAnyTrailingCharacters(): void
    {
        $this->buffer = new LineBuffer('foo,bar,/ignoreme');
        $field = $this->buffer->eat()->eat()->textField();
        $this->assertEquals('', $field);
    }

    public function testTextFieldTrailingWhitespaceRetained(): void
    {
        $this->buffer = new LineBuffer('foo,bar,baz                   ');
        $field = $this->buffer->eat()->eat()->textField();
        $this->assertEquals('baz                   ', $field);
    }

    public function testFieldTrailingWhitespaceRetailed(): void
    {
        $this->buffer = new LineBuffer('foo,bar,baz                   /');
        $field = $this->buffer->eat()->eat()->field();
        $this->assertEquals('baz                   ', $field);
    }

    public function testIsEndOfLineReturnsFalseWhenNotStarted(): void
    {
        $this->assertFalse($this->buffer->isEndOfLine());
    }

    public function testIsEndOfLineReturnsFalseWhenPartWayThrough(): void
    {
        $this->buffer->eat();
        $this->assertFalse($this->buffer->isEndOfLine());
    }

    public function testIsEndOfLineReturnsFalseWhenOnLastField(): void
    {
        $this->buffer->eat()->eat();
        $this->assertFalse($this->buffer->isEndOfLine());
    }

    public function testIsEndOfLineReturnsTrueWhenAtEndOfBuffer(): void
    {
        $this->buffer->eat()->eat()->eat();
        $this->assertTrue($this->buffer->isEndOfLine());
    }

    public function testIsEndOfLineReturnsTrueAfterReadingTextFieldAndAdvancing(): void
    {
        $this->buffer->eat()->textField();
        $this->buffer->eat();
        $this->assertTrue($this->buffer->isEndOfLine());
    }

    public function testIsEndOfLineReturnsTrueAfterReadingTextFieldAsFirstFieldAndAdvancing(): void
    {
        $this->buffer->textField();
        $this->buffer->eat();
        $this->assertTrue($this->buffer->isEndOfLine());
    }

    public function testIsEndOfLineReturnsTrueAfterReadingDefaultedTextFieldAndAdvancing(): void
    {
        $this->buffer = new LineBuffer('foo,bar,/');
        $field = $this->buffer->eat()->eat()->textField();
        $this->buffer->eat();
        $this->assertTrue($this->buffer->isEndOfLine());
    }

    public function testThrowsExceptionWhenAccessingNormalFieldAtEndOfUnterminatedLine(): void
    {
        $this->buffer = new LineBuffer('foo,bar,baz quux');

        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Cannot access last (non-text) field on unterminated input line.');
        $field = $this->buffer->eat()->eat()->field();
    }

    public function testThrowsExceptionWhenAccessingNormalFieldAtEndOfUnterminedSingleFieldLine(): void
    {
        $this->buffer = new LineBuffer('foo');

        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Cannot access last (non-text) field on unterminated input line.');
        $field = $this->buffer->field();
    }

    public function testCanAccessTextFieldAtEndOfSingleFieldLineWithoutRespectToEndingSlash(): void
    {
        $this->buffer = new LineBuffer('foo');
        $this->assertEquals('foo', $this->buffer->textField());
    }

    public function testCanAccessNormalFieldAtEndOfSingleFieldLineIfProperlyTerminated(): void
    {
        $this->buffer = new LineBuffer('foo/');
        $this->assertEquals('foo', $this->buffer->field());
    }

    public function testThrowsExceptionWhenAdvancingBeyondTheEndOfTheBuffer(): void
    {
        $this->buffer->eat()->eat()->eat();

        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Cannot advance beyond the end of the buffer.');
        $this->buffer->eat();
    }

    public function testThrowsExceptionWhenTryingToReadNormalFieldOnceAtEndOfTheBuffer(): void
    {
        $this->buffer->eat()->eat()->eat();

        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Cannot access fields at the end of the buffer.');
        $this->buffer->field();
    }

    public function testThrowsExceptionWhenTryingToReadTextFieldOnceAtEndOfTheBuffer(): void
    {
        $this->buffer->eat()->eat()->eat();

        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Cannot access fields at the end of the buffer.');
        $this->buffer->textField();
    }

    public function testThrowsExceptionWhenTryingToReadContinuedTextOnceAtEndOfTheBuffer(): void
    {
        $this->buffer->eat()->eat()->eat();

        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Cannot access fields at the end of the buffer.');
        $this->buffer->continuedTextField();
    }

}
