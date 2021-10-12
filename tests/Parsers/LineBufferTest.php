<?php

namespace STS\Bai2\Parsers;

use PHPUnit\Framework\TestCase;

final class LineBufferTest extends TestCase
{

    private LineBuffer $buffer;

    protected function setUp(): void
    {
        $this->buffer = new LineBuffer('foo,bar,baz/');
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

    /**
     * @testWith ["foo,bar,baz,foo,bar,baz,foo,bar,baz,foo,bar,baz,foo,bar,baz,foo,bar,baz,foo,bar,baz/"]
     *           ["foo,bar,baz/                                                                        "]
     */
    public function testThrowsWhenContructWithLineLongerThanPhsyicalLength(string $input): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Input line length exceeds requested physical record length.');
        $this->buffer = new LineBuffer($input, 80);
    }

    /**
     * @testWith ["foo,bar,baz,foo,bar,baz,foo,bar,baz,foo,bar,baz,foo,bar,baz,foo,bar,baz,foo,bar,baz/"]
     *           ["foo,bar,baz/                                                                        "]
     */
    public function testThrowsWhenSetPhysicalRecordLengthExceedingOriginalLineLength(string $input): void
    {
        $this->buffer = new LineBuffer($input);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Input line length exceeds requested physical record length.');
        $this->buffer->setPhysicalRecordLength(80);
    }

    public function testThrowsWhenTryingToSetPhysicalRecordLengthToNullAfterConstruction(): void
    {
        $this->buffer = new LineBuffer('foo,bar,baz/', 80);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot set physical record length to null after it has been non-null.');
        $this->buffer->setPhysicalRecordLength(null);
    }

    public function testThrowsWhenTryingToSetPhysicalRecordLengthToNullAfterSetingToNonNull(): void
    {
        $this->buffer = new LineBuffer('foo,bar,baz/');
        $this->buffer->setPhysicalRecordLength(80);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot set physical record length to null after it has been non-null.');
        $this->buffer->setPhysicalRecordLength(null);
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

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot access last (non-text) field on unterminated input line.');
        $field = $this->buffer->eat()->eat()->field();
    }

    public function testThrowsExceptionWhenAccessingNormalFieldAtEndOfUnterminedSingleFieldLine(): void
    {
        $this->buffer = new LineBuffer('foo');

        $this->expectException(\Exception::class);
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

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot advance beyond the end of the buffer.');
        $this->buffer->eat();
    }

    public function testThrowsExceptionWhenTryingToReadNormalFieldOnceAtEndOfTheBuffer(): void
    {
        $this->buffer->eat()->eat()->eat();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot access fields at the end of the buffer.');
        $this->buffer->field();
    }

    public function testThrowsExceptionWhenTryingToReadTextFieldOnceAtEndOfTheBuffer(): void
    {
        $this->buffer->eat()->eat()->eat();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot access fields at the end of the buffer.');
        $this->buffer->textField();
    }

}
