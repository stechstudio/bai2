<?php

declare(strict_types=1);

namespace STS\Bai2\Parsers;

use PHPUnit\Framework\TestCase;

use STS\Bai2\Exceptions\MalformedInputException;
use STS\Bai2\Exceptions\ParseException;

final class LineParserTest extends TestCase
{

    private static string $headerLine = '01,SENDR1,RECVR1,210616,1700,01,50,10,2/';

    private static string $headerWithDefaultedPhysicalRecordLengthField = '01,SENDR1,RECVR1,210616,1700,01,,10,2/';

    private static string $headerInputWithUnterminatedVersionFieldProducer = '01,SENDR1,RECVR1,210616,1700,01,80,10,2';

    private static string $transactionLine = "16,409,10000,D,3,1,1000,5,10000,30,25000,123456789,987654321,The following character is, of all the path separation characters I've ever used, my absolute favorite: /";

    private static string $transactionLineDefaultedText = '16,409,10000,D,7,1,100,2,1000,3,10000,4,100000,5,1000000,6,10000000,7,100000000,123456789,987654321,/';

    private static string $continuationTextLine = '88, and, as I was saying, I have been continued mid-sentence.';

    private static string $continuationTextSlashLine = '88,/';

    public function testPeekReturnsNextFieldWithoutConsumingIt(): void
    {
        $parser = new LineParser(self::$headerLine);
        $this->assertEquals('01', $parser->peek());
        $this->assertEquals('01', $parser->peek());
    }

    public function testPeekCanPeekIntoALaterField(): void
    {
        $parser = new LineParser(self::$headerLine);
        $parser->drop(3);

        $this->assertEquals('210616', $parser->peek());
        $this->assertEquals('210616', $parser->peek());
    }

    public function testPeekCanPeekIntoDefaultedField(): void
    {
        $parser = new LineParser(self::$headerWithDefaultedPhysicalRecordLengthField);
        $parser->drop(6);

        $this->assertEquals('', $parser->peek());
        $this->assertEquals('', $parser->peek());
    }

    public function testShiftReturnsNextFieldAndConsumesIt(): void
    {
        $parser = new LineParser(self::$headerLine);
        $this->assertEquals('01', $parser->shift());
        $this->assertEquals('SENDR1', $parser->peek());
    }

    public function testShiftCanExtractALaterField(): void
    {
        $parser = new LineParser(self::$headerLine);
        $parser->drop(3);

        $this->assertEquals('210616', $parser->shift());
        $this->assertEquals('1700', $parser->peek());
    }

    public function testShiftCanExtractADefaultedField(): void
    {
        $parser = new LineParser(self::$headerWithDefaultedPhysicalRecordLengthField);
        $parser->drop(6);

        $this->assertEquals('', $parser->shift());
        $this->assertEquals('10', $parser->peek());
    }

    public function testDropReturnsNumFieldsRequestedAndConsumesThem(): void
    {
        $parser = new LineParser(self::$headerLine);
        $this->assertEquals(['01', 'SENDR1', 'RECVR1'], $parser->drop(3));
        $this->assertEquals('210616', $parser->peek());
    }

    public function testDropCanIncludeADefaultedField(): void
    {
        $parser = new LineParser(self::$headerWithDefaultedPhysicalRecordLengthField);

        $this->assertEquals(
            ['01', 'SENDR1', 'RECVR1', '210616', '1700', '01', ''],
            $parser->drop(7)
        );
        $this->assertEquals('10', $parser->peek());
    }

    public function testShiftTextReturnsTheRemainderOfBufferAsText(): void
    {
        $parser = new LineParser(self::$transactionLine);

        // consume and discard the non-text fields
        $parser->drop(13);

        // Note: per the spec, text fields are always at the end of a line, and
        // you have to at least partially parse a line first to know at what
        // position that text field begins.
        $this->assertEquals(
            "The following character is, of all the path separation characters I've ever used, my absolute favorite: /",
            $parser->shiftText()
        );
    }

    public function testShiftOffDefaultedTextField(): void
    {
        $parser = new LineParser(self::$transactionLineDefaultedText);

        // consume and discard the non-text fields
        $parser->drop(21);

        // Note: per the spec, text fields which are defaulted are denominated
        // with ,/
        $this->assertEquals('', $parser->shiftText());
    }

    public function testShiftContinuedTextReturnsTheRemainderOfBufferAsText(): void
    {
        $parser = new LineParser(self::$continuationTextLine);
        $parser->drop(1);

        $this->assertEquals(
            ' and, as I was saying, I have been continued mid-sentence.',
            $parser->shiftContinuedText()
        );
    }

    public function testShiftContinuedTextDoesNotTreatSlashSpecially(): void
    {
        $parser = new LineParser(self::$continuationTextSlashLine);
        $parser->drop(1);

        $this->assertEquals('/', $parser->shiftContinuedText());
    }

    public function testHasMoreWhenLineHasMore(): void
    {
        $parser = new LineParser(self::$headerLine);
        $parser->drop(8);
        $this->assertTrue($parser->hasMore());
    }

    public function testHasMoreWhenLineHasNoMore(): void
    {
        $parser = new LineParser(self::$headerLine);
        $parser->drop(9);
        $this->assertFalse($parser->hasMore());
    }

    public function testIsEndOfLineWhenLineHasMore(): void
    {
        $parser = new LineParser(self::$headerLine);
        $parser->drop(8);
        $this->assertFalse($parser->isEndOfLine());
    }

    public function testIsEndOfLineWhenLineHasNoMore(): void
    {
        $parser = new LineParser(self::$headerLine);
        $parser->drop(9);
        $this->assertTrue($parser->isEndOfLine());
    }

    public function testConstructWithoutPhysicalRecordLengthCorrectlyHandlesPadding(): void
    {
        $parser = new LineParser(self::$continuationTextLine . '         ');
        $parser->shift();
        $this->assertEquals(
            ' and, as I was saying, I have been continued mid-sentence.         ',
            $parser->shiftText()
        );
        $this->assertTrue($parser->isEndOfLine());
    }

    public function testConstructWithPhysicalRecordLengthCorrectlyHandlesPadding(): void
    {
        $parser = new LineParser(self::$continuationTextLine . '         ', 70);
        $parser->shift();
        $this->assertEquals(
            ' and, as I was saying, I have been continued mid-sentence.',
            $parser->shiftText()
        );
        $this->assertTrue($parser->isEndOfLine());
    }

    public function testSettingPhysicalRecordLengthCorrectlyHandlesPadding(): void
    {
        $parser = new LineParser(self::$continuationTextLine . '         ');
        $parser->setPhysicalRecordLength(70);
        $parser->shift();
        $this->assertEquals(
            ' and, as I was saying, I have been continued mid-sentence.',
            $parser->shiftText()
        );
        $this->assertTrue($parser->isEndOfLine());
    }

    public function testThrowsIfPeekingPastEndOfLine(): void
    {
        $parser = new LineParser(self::$headerLine);
        $parser->drop(9);

        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Cannot access fields at the end of the buffer.');
        $parser->peek();
    }

    public function testThrowsIfShiftingPastEndOfLine(): void
    {
        $parser = new LineParser(self::$headerLine);
        $parser->shift();
        $parser->shift();
        $parser->shift();
        $parser->shift();
        $parser->shift();
        $parser->shift();
        $parser->shift();
        $parser->shift();
        $parser->shift();

        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Cannot access fields at the end of the buffer.');
        $parser->shift();
    }

    public function testThrowsIfDroppingMoreThanAvailableInBuffer(): void
    {
        $parser = new LineParser(self::$headerLine);

        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Cannot access fields at the end of the buffer.');
        $parser->drop(10);
    }

    public function testThrowsIfShiftingTextPastEndOfLine(): void
    {
        $parser = new LineParser(self::$transactionLine);

        // consume and discard the non-text fields
        $parser->drop(13);

        // shift off the remaining text field
        $parser->shiftText();

        // make it go boom!
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Cannot access fields at the end of the buffer.');
        $parser->shiftText();
    }

    public function testThrowsIfShiftingContinuedTextPastEndOfLine(): void
    {
        $parser = new LineParser(self::$continuationTextLine);
        $parser->drop(1);
        $parser->shiftContinuedText();

        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Cannot access fields at the end of the buffer.');
        $parser->shiftContinuedText();
    }

    public function testThrowsIfPeekingAtAnUnterminatedField(): void
    {
        $parser = new LineParser(self::$headerInputWithUnterminatedVersionFieldProducer);
        $parser->drop(8);

        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Cannot access last (non-text) field on unterminated input line.');
        $parser->peek();
    }

    public function testThrowsIfShiftingAnUnterminatedField(): void
    {
        $parser = new LineParser(self::$headerInputWithUnterminatedVersionFieldProducer);
        $parser->drop(8);

        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Cannot access last (non-text) field on unterminated input line.');
        $parser->shift();
    }

    public function testThrowsIfDroppingAnUnterminatedField(): void
    {
        $parser = new LineParser(self::$headerInputWithUnterminatedVersionFieldProducer);

        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Cannot access last (non-text) field on unterminated input line.');
        $parser->drop(9);
    }

    public function testThrowsWhenContructWithLineLongerThanPhysicalLength(): void
    {
        $this->expectException(MalformedInputException::class);
        $this->expectExceptionMessage('Input line length exceeds requested physical record length.');
        $parser = new LineParser(self::$headerLine, 10);
        $parser = new LineParser(
            "88, characters I've ever used, my absolute favorite: /                           ",
            80
        );
    }

    public function testThrowsWhenSetPhysicalRecordLengthExceedingOriginalLineLength(): void
    {
        $parser = new LineParser("88, characters I've ever used, my absolute favorite: /                           ");
        $this->expectException(MalformedInputException::class);
        $this->expectExceptionMessage('Input line length exceeds requested physical record length.');
        $parser->setPhysicalRecordLength(80);
    }

}
