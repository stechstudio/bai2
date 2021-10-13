<?php

namespace STS\Bai2\Parsers;

use PHPUnit\Framework\TestCase;

final class MultilineParserTest extends TestCase
{

    public function headerInputProducer(): array
    {
        return [
            ['01,SENDR1,RECVR1,210616,1700,01,80,10,2/'],
            [[
                '01,SENDR1,RECVR1/',
                '88,210616,1700/',
                '88,01,80,10,2/',
            ]],
            [[
                '01/',
                '88,SENDR1/',
                '88,RECVR1/',
                '88,210616/',
                '88,1700/',
                '88,01/',
                '80,10/',
                '80,80/',
                '88,2/',
            ]],
        ];
    }

    // TODO(zmd): use me in some tests!
    public function headerWithDefaultedPhysicalRecordLengthFieldProducer(): array
    {
        return [
            ['01,SENDR1,RECVR1,210616,1700,01,,10,2/'],
            [[
                '01,SENDR1,RECVR1/',
                '88,210616,1700/',
                '88,01,,10,2/',
            ]],
            [[
                '01/',
                '88,SENDR1/',
                '88,RECVR1/',
                '88,210616/',
                '88,1700/',
                '88,01/',
                '88,/',
                '88,10/',
                '88,2/',
            ]],
        ];
    }

    public function transactionInputProducer(): array
    {
        return [
            ["16,003,10000,D,3,1,1000,5,10000,30,25000,123456789,987654321,The following character is, of all the path separation characters I've ever used, my absolute favorite: /"],
            [[
                '16,003,10000,D/',
                '88,3,1,1000,5,10000,30/',
                '88,25000,123456789,987654321,The following ',
                '88,character is, of all the path separation ',
                "88,characters I've ever used, my absolute favorite: /",
            ]],
            [[
                '16/',
                '88,003/',
                '88,10000/',
                '88,D/',
                '88,3/',
                '88,1/',
                '88,1000/',
                '88,5/',
                '88,10000/',
                '88,30/',
                '88,25000/',
                '88,123456789/',
                '88,987654321/',
                '88,The',
                '88, following ',
                '88,character ',
                '88,is, ',
                '88,of all the path separation ',
                '88,characters',
                "88, I've ever ",
                '88,used,',
                '88, my absolute favorite:',
                '88, /',
            ]],

            //
            // TODO(zmd): the following data should be usable for shifting off
            //   a single slash from continued text, but we need to do a couple
            //   things to enable that.
            //
            // [[
            //     '16/',
            //     '88,003/',
            //     '88,10000/',
            //     '88,D/',
            //     '88,3/',
            //     '88,1/',
            //     '88,1000/',
            //     '88,5/',
            //     '88,10000/',
            //     '88,30/',
            //     '88,25000/',
            //     '88,123456789/',
            //     '88,987654321/',
            //     '88,The',
            //     '88, following ',
            //     '88,character ',
            //     '88,is, ',
            //     '88,of all the path separation ',
            //     '88,characters',
            //     "88, I've ever ",
            //     '88,used,',
            //     '88, my absolute favorite: ',
            //     '88,/',
            // ]],
        ];
    }

    public function transactionWithDefaultedTextInputProducer(): array
    {
        return [
            ['16,003,10000,D,7,1,100,2,1000,3,10000,4,100000,5,1000000,6,10000000,7,100000000,123456789,987654321,/'],
            [[
                '16,003,10000/',
                '88,D,7/',
                '88,1,100/',
                '88,2,1000/',
                '88,3,10000/',
                '88,4,100000/',
                '88,5,1000000/',
                '88,6,10000000/',
                '88,7,100000000/',
                '88,123456789,987654321,/',
            ]],
            [[
                '16/',
                '88,003/',
                '88,10000/',
                '88,D/',
                '88,7/',
                '88,1/',
                '88,100/',
                '88,2/',
                '88,1000/',
                '88,3/',
                '88,10000/',
                '88,4/',
                '88,100000/',
                '88,5/',
                '88,1000000/',
                '88,6/',
                '88,10000000/',
                '88,7/',
                '88,100000000/',
                '88,123456789/',
                '88,987654321/',
                '88,/',
            ]],
        ];
    }

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

    /**
     * @dataProvider headerInputProducer
     */
    public function testPeekReturnsNextFieldWithoutConsumingIt($input): void
    {
        $this->withParser($input, function ($parser) {
            $this->assertEquals('01', $parser->peek());
            $this->assertEquals('01', $parser->peek());
        });
    }

    /**
     * @dataProvider headerInputProducer
     */
    public function testPeekCanPeekIntoALaterField($input): void
    {
        $this->withParser($input, function ($parser) {
            $parser->drop(3);

            $this->assertEquals('210616', $parser->peek());
            $this->assertEquals('210616', $parser->peek());
        });
    }

    /**
     * @dataProvider headerWithDefaultedPhysicalRecordLengthFieldProducer
     */
    public function testPeekCanPeekIntoDefaultedField($input): void
    {
        $this->withParser($input, function ($parser) {
            $parser->drop(6);

            $this->assertEquals('', $parser->peek());
            $this->assertEquals('', $parser->peek());
        });
    }

    /**
     * @dataProvider headerInputProducer
     */
    public function testShiftReturnsNextFieldAndConsumesIt($input): void
    {
        $this->withParser($input, function ($parser) {
            $this->assertEquals('01', $parser->shift());
            $this->assertEquals('SENDR1', $parser->peek());
        });
    }

    /**
     * @dataProvider headerInputProducer
     */
    public function testShiftCanExtractALaterField($input): void
    {
        $this->withParser($input, function ($parser) {
            $parser->drop(3);

            $this->assertEquals('210616', $parser->shift());
            $this->assertEquals('1700', $parser->peek());
        });
    }

    // TODO(zmd): testShiftCanExtractADefaultedField

    /**
     * @dataProvider headerInputProducer
     */
    public function testDropReturnsNumFieldsRequestedAndConsumesThem($input): void
    {
        $this->withParser($input, function ($parser) {
            $this->assertEquals(['01', 'SENDR1', 'RECVR1'], $parser->drop(3));
            $this->assertEquals('210616', $parser->peek());
        });
    }

    // testDropCanIncludeADefaultedField

    /**
     * @dataProvider transactionInputProducer
     */
    public function testShiftTextReturnsTheRemainderOfBufferAsText($input): void
    {
        $this->withParser($input, function ($parser) {
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

    /**
     * @dataProvider transactionWithDefaultedTextInputProducer
     */
    public function testShiftOffDefaultedTextField($input): void
    {
        $this->withParser($input, function ($parser) {
            $parser->drop(21);
            $this->assertEquals('', $parser->shiftText());
        });
    }

    /**
     * @dataProvider headerInputProducer
     */
    public function testThrowsIfPeekingPastEndOfLine($input): void
    {
        $this->withParser($input, function ($parser) {
            $parser->drop(9);

            $this->expectException(\Exception::class);
            $this->expectExceptionMessage('Cannot access fields at the end of the buffer.');
            $parser->peek();
        });
    }

    /**
     * @dataProvider headerInputProducer
     */
    public function testThrowsIfShiftingPastEndOfLine($input): void
    {
        $this->withParser($input, function ($parser) {
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

    /**
     * @dataProvider headerInputProducer
     */
    public function testThrowsIfDroppingMoreThanAvailableInBuffer($input): void
    {
        $this->withParser($input, function ($parser) {
            $this->expectException(\Exception::class);
            $this->expectExceptionMessage('Cannot access fields at the end of the buffer.');
            $parser->drop(10);
        });
    }

    /**
     * @dataProvider transactionInputProducer
     */
    public function testThrowsIfShiftingTextPastEndOfLine($input): void
    {
        $this->withParser($input, function ($parser) {
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
