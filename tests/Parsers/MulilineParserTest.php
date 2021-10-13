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
        ',25000,123456789,987654321,The following',
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

    public function testPeekReturnsNextFieldWithoutConsumingIt(): void
    {
        $this->withParser(self::$headerLine, function ($parser) {
            $this->assertEquals('01', $parser->peek());
            $this->assertEquals('01', $parser->peek());
        });
    }

    public function testShiftReturnsNextFieldAndConsumesIt(): void
    {
        $this->withParser(self::$headerLine, function ($parser) {
            $this->assertEquals('01', $parser->shift());
            $this->assertEquals('SENDR1', $parser->peek());
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

    // TODO(zmd): test all the main methods without continue first (like
    //   ::drop(), etc.); behavior should match line buffer exactly when no
    //   continuations are used

    // TODO(zmd): test ::continue() before testing the various function's
    //   behavior in light of the use of ::continue()?

    // TODO(zmd): test that ::continue() skips over the record type field (so
    //   the next ::peek() or ::shift() will NOT be '88')

    public function testPeekCanPeekIntoAContinuedLine(): void
    {
        $this->withParser(self::$headerLineContinued, function ($parser) {
            $parser->drop(3);

            $this->assertEquals('210616', $parser->peek());
            $this->assertEquals('210616', $parser->peek());
        });
    }

}
