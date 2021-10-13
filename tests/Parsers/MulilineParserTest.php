<?php

namespace STS\Bai2\Parsers;

use PHPUnit\Framework\TestCase;

final class MultilineParserTest extends TestCase
{

    private static string $headerLine = '01,SENDR1,RECVR1,210616,1700,01,80,10,2/';

    private static array $headerLineContinued = [
        '01,SENDR1,RECVR1/',
        '88,210616,1700/',
        '88,01,80,10,2/'
    ];

    private static string $transactionLine = "16,003,10000,D,3,1,1000,5,10000,30,25000,123456789,987654321,The following character is, of all the path separation characters I've ever used, my absolute favorite: /";

    private static array $transactionLineContinued = [
        '16,003,10000,D/',
        '88,3,1,1000,5,10000,30/',
        '88,25000,123456789,987654321,The following',
        '88,character is, of all the path separation ',
        "88,characters I've ever used, my absolute favorite: /",
    ];

    private static string $transactionLineDefaultedText = "16,003,10000,D,3,1,1000,5,10000,30,25000,123456789,987654321,/";

    private function withParser(string|array $input, callable $callable): void
    {
        if (is_string($input)) {
            $callable(new MultilineParser($input));
        } else {
            $parser = new MultilineParser(array_shift($input));
            foreach ($input as $line) {
                $parser->continue($line);
            }

            $callable($parser);
        }
    }

    public function testContinueCanBeUsedToAddContinuationRecords(): void
    {
        $this->withParser('01,SENDR1,RECVR1/', function ($parser) {
            // only the 3 fields
            $this->assertEquals(['01', 'SENDR1', 'RECVR1'], $parser->drop(3));

            // can shift no more for we did not ::continue() any more
            try {
                $parser->shift();
            } catch (\Exception $e) {
                $this->assertEquals(
                    'Cannot access fields at the end of the buffer.',
                    $e->getMessage()
                );
            }
        });

        $this->withParser('01,SENDR1,RECVR1/', function ($parser) {
            $parser->continue('88,210616,1700/');
            $parser->continue('88,01,80,10,2/');

            // we have the 3 fields
            $this->assertEquals(['01', 'SENDR1', 'RECVR1'], $parser->drop(3));

            // and more!
            $firstContinuedField = $parser->shift();

            $this->assertNotEquals(
                '88',
                $firstContinuedField,
                '88 record type field should have been discarded.'
            );

            $this->assertEquals('210616', $firstContinuedField);
            $this->assertEquals(
                ['1700', '01', '80', '10', '2'],
                $parser->drop(5)
            );
        });
    }

    public function testPeekReturnsNextFieldWithoutConsumingIt(): void
    {
        $this->withParser(self::$headerLine, function ($parser) {
            $this->assertEquals('01', $parser->peek());
            $this->assertEquals('01', $parser->peek());
        });
    }

    public function testPeekCanPeekIntoALaterField(): void
    {
        $this->withParser(self::$headerLineContinued, function ($parser) {
            $parser->drop(3);

            $this->assertEquals('210616', $parser->peek());
            $this->assertEquals('210616', $parser->peek());
        });
    }

    public function testShiftReturnsNextFieldAndConsumesIt(): void
    {
        $this->withParser(self::$headerLine, function ($parser) {
            $this->assertEquals('01', $parser->shift());
            $this->assertEquals('SENDR1', $parser->peek());
        });
    }

    public function testShiftCanExtractALaterField(): void
    {
        $this->withParser(self::$headerLine, function ($parser) {
            $parser->drop(3);

            $this->assertEquals('210616', $parser->shift());
            $this->assertEquals('1700', $parser->peek());
        });
    }

    public function testDropReturnsNumFieldsRequestedAndConsumesThem(): void
    {
        $this->withParser(self::$headerLine, function ($parser) {
            $this->assertEquals(['01', 'SENDR1', 'RECVR1'], $parser->drop(3));
            $this->assertEquals('210616', $parser->peek());
        });
    }

    public function testShiftTextReturnsTheRemainderOfBufferAsText(): void
    {
        $this->withParser(self::$transactionLine, function ($parser) {
            // consume and discard the non-text fields
            $parser->drop(13);

            // Note: per the spec, text fields are always at the end of a line,
            // and you have to at least partially parse a line first to know at
            // what position that text field begins.
            $this->assertEquals(
                "The following character is, of all the path separation characters I've ever used, my absolute favorite: /",
                $parser->shiftText()
            );
        });
    }

    public function testThrowsIfPeekingPastEndOfLine(): void
    {
        $this->withParser(self::$headerLine, function ($parser) {
            $parser->drop(9);

            $this->expectException(\Exception::class);
            $this->expectExceptionMessage('Cannot access fields at the end of the buffer.');
            $parser->peek();
        });
    }

    public function testThrowsIfShiftingPastEndOfLine(): void
    {
        $this->withParser(self::$headerLine, function ($parser) {
            $parser->shift();
            $parser->shift();
            $parser->shift();
            $parser->shift();
            $parser->shift();
            $parser->shift();
            $parser->shift();
            $parser->shift();
            $parser->shift();

            $this->expectException(\Exception::class);
            $this->expectExceptionMessage('Cannot access fields at the end of the buffer.');
            $parser->shift();
        });
    }

    public function testThrowsIfDroppingMoreThanAvailableInBuffer(): void
    {
        $this->withParser(self::$headerLine, function ($parser) {
            $this->expectException(\Exception::class);
            $this->expectExceptionMessage('Cannot access fields at the end of the buffer.');
            $parser->drop(10);
        });
    }

    public function testThrowsIfShiftingTextPastEndOfLine(): void
    {
        $this->withParser(self::$transactionLine, function ($parser) {
            // consume and discard the non-text fields
            $parser->drop(13);

            // shift off the remaining text field
            $parser->shiftText();

            // make it go boom!
            $this->expectException(\Exception::class);
            $this->expectExceptionMessage('Cannot access fields at the end of the buffer.');
            $parser->shiftText();
        });
    }

}
