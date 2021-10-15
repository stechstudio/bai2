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
                '88,10/',
                '88,80/',
                '88,2/',
            ]],
        ];
    }

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

    public function headerInputWithUnterminatedVersionFieldProducer(): array
    {
        return [
            ['01,SENDR1,RECVR1,210616,1700,01,80,10,2'],
            [[
                '01,SENDR1,RECVR1/',
                '88,210616,1700/',
                '88,01,80,10,2',
            ]],
            [[
                '01/',
                '88,SENDR1/',
                '88,RECVR1/',
                '88,210616/',
                '88,1700/',
                '88,01/',
                '88,10/',
                '88,80/',
                '88,2',
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
                '88, my absolute favorite: ',
                '88,/',
            ]],
        ];
    }

    public function transactionWithPaddedInputForNoPhysicalRecordLengthProducer(): array
    {
        return [
            [[
                '16,003,10000,D/                                                                 ',
                '88,3,1,1000,5,10000,30/                                                         ',
                '88,25000,123456789,987654321,The following                                      ',
                '88, character is, of all the path separation                                    ',
                "88, characters I've ever used, my absolute favorite: /                          ",
            ]],
            [[
                '16,003,10000,D/-----------------------------------------------------------------',
                '88,3,1,1000,5,10000,30/+++++++++++++++++++++++++++++++++++++++++++++++++++++++++',
                '88,25000,123456789,987654321,The following                                      ',
                '88, character is, of all the path separation                                    ',
                "88, characters I've ever used, my absolute favorite: /                          ",
            ]],
        ];
    }

    public function transactionWithPaddedInputProducer(): array
    {
        return [
            [[
                '16,003,10000,D,3,1,1000,5,10000,30,25000,123456789,987654321,The following chara',
                "88,cter is, of all the path separation characters I've ever used, my absolute fa",
                '88,vorite: /                                                                    '
            ]],
            [[
                '16,003,10000,D/                                                                 ',
                '88,3,1,1000,5,10000,30/                                                         ',
                '88,25000,123456789,987654321,The following                                      ',
                '88, character is, of all the path separation                                    ',
                "88, characters I've ever used, my absolute favorite: /                          ",
            ]],
            [[
                '16,003,10000,D/-----------------------------------------------------------------',
                '88,3,1,1000,5,10000,30/+++++++++++++++++++++++++++++++++++++++++++++++++++++++++',
                '88,25000,123456789,987654321,The following                                      ',
                '88, character is, of all the path separation                                    ',
                "88, characters I've ever used, my absolute favorite: /                          ",
            ]],
            [[
                '16/                                                                             ',
                '88,003/                                                                         ',
                '88,10000/                                                                       ',
                '88,D/                                                                           ',
                '88,3/                                                                           ',
                '88,1/                                                                           ',
                '88,1000/                                                                        ',
                '88,5/                                                                           ',
                '88,10000/                                                                       ',
                '88,30/                                                                          ',
                '88,25000/                                                                       ',
                '88,123456789/                                                                   ',
                '88,987654321/                                                                   ',
                '88,The                                                                          ',
                '88, following                                                                   ',
                '88, character                                                                   ',
                '88, is,                                                                         ',
                '88, of all the path separation                                                  ',
                '88, characters                                                                  ',
                "88, I've ever                                                                   ",
                '88, used,                                                                       ',
                '88, my absolute favorite:                                                       ',
                '88, /                                                                           ',
            ]],
            [[
                '16/-----------------------------------------------------------------------------',
                '88,003/+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++',
                '88,10000/=======================================================================',
                '88,D/)))))))))))))))))))))))))))))))))))))))))))))))))))))))))))))))))))))))))))',
                '88,3/(((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((',
                '88,1/]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]',
                '88,1000/[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[',
                '88,5/;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;',
                '88,10000/:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::',
                '88,30/,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,',
                '88,25000/.......................................................................',
                '88,123456789////////////////////////////////////////////////////////////////////',
                '88,987654321/&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&',
                '88,The                                                                          ',
                '88, following                                                                   ',
                '88, character                                                                   ',
                '88, is,                                                                         ',
                '88, of all the path separation                                                  ',
                '88, characters                                                                  ',
                "88, I've ever                                                                   ",
                '88, used,                                                                       ',
                '88, my absolute favorite:                                                       ',
                '88, /                                                                           ',
            ]],
            [[
                '16/---------------------------------------------------------',
                '88,003/+++++++++++++++++++++++++++++++++++++++++++++++++++++',
                '88,10000/===================================================',
                '88,D/)))))))))))))))))))))))))))))))))))))))))))))))))))))))',
                '88,3/(((((((((((((((((((((((((((((((((((((((((((((((((((((((',
                '88,1/]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]',
                '88,1000/[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[',
                '88,5/;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;',
                '88,10000/:::::::::::::::::::::::::::::::::::::::::::::::::::',
                '88,30/,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,',
                '88,25000/...................................................',
                '88,123456789////////////////////////////////////////////////',
                '88,987654321/&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&',
                '88,The                                                      ',
                '88, following                                               ',
                '88, character                                               ',
                '88, is,                                                     ',
                '88, of all the path separation                              ',
                '88, characters                                              ',
                "88, I've ever                                               ",
                '88, used,                                                   ',
                '88, my absolute favorite:                                   ',
                '88, /                                                       ',
            ]],
        ];
    }

    public function transactionWithPaddedInputTooLongProducer(): array
    {
        return [
            ["16,003,10000,D,3,1,1000,5,10000,30,25000,123456789,987654321,The following character is, of all the path separation characters I've ever used, my absolute favorite: /"],
            [[
                '16,003,10000,D/                                                                 ',
                '88,3,1,1000,5,10000,30/                                                         ',
                '88,25000,123456789,987654321,The following                                      ',
                '88, character is, of all the path separation                                    ',
                "88, characters I've ever used, my absolute favorite: /                           ",
            ]],
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
        $this->withPhysicalRecordLengthParser($input, null, $callable);
    }

    private function withPhysicalRecordLengthParser(
        string|array $input,
        ?int $physicalRecordLength,
        callable $callable
    ): void {
        if (is_string($input)) {
            $callable(new MultilineParser($input, $physicalRecordLength));
        } else {
            $parser = new MultilineParser(
                array_shift($input),
                $physicalRecordLength
            );
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

    public function testContinueCanBeUsedToAddContinuationRecordsAfterReadingFields(): void
    {
        $this->withParser('01,SENDR1,RECVR1/', function ($parser) {
            // for a limited time...
            $this->assertEquals(['01', 'SENDR1', 'RECVR1'], $parser->drop(3));

            // but wait, there's more!
            $parser->continue('88,210616,1700/');
            $this->assertEquals('210616', $parser->shift(3));
            $this->assertEquals('1700', $parser->shift(3));

            // order within the next 24 hours and...
            $parser->continue('88,01,80,10,2/');
            $this->assertEquals(['01', '80', '10', '2'], $parser->drop(4));
        });
    }

    /**
     * @dataProvider headerInputProducer
     */
    public function testPeekReturnsNextFieldWithoutConsumingIt(string|array $input): void
    {
        $this->withParser($input, function ($parser) {
            $this->assertEquals('01', $parser->peek());
            $this->assertEquals('01', $parser->peek());
        });
    }

    /**
     * @dataProvider headerInputProducer
     */
    public function testPeekCanPeekIntoALaterField(string|array $input): void
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
    public function testPeekCanPeekIntoDefaultedField(string|array $input): void
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
    public function testShiftReturnsNextFieldAndConsumesIt(string|array $input): void
    {
        $this->withParser($input, function ($parser) {
            $this->assertEquals('01', $parser->shift());
            $this->assertEquals('SENDR1', $parser->peek());
        });
    }

    /**
     * @dataProvider headerInputProducer
     */
    public function testShiftCanExtractALaterField(string|array $input): void
    {
        $this->withParser($input, function ($parser) {
            $parser->drop(3);

            $this->assertEquals('210616', $parser->shift());
            $this->assertEquals('1700', $parser->peek());
        });
    }

    /**
     * @dataProvider headerWithDefaultedPhysicalRecordLengthFieldProducer
     */
    public function testShiftCanExtractADefaultedField(string|array $input): void
    {
        $this->withParser($input, function ($parser) {
            $parser->drop(6);

            $this->assertEquals('', $parser->shift());
            $this->assertEquals('10', $parser->peek());
        });
    }

    /**
     * @dataProvider headerInputProducer
     */
    public function testDropReturnsNumFieldsRequestedAndConsumesThem(string|array $input): void
    {
        $this->withParser($input, function ($parser) {
            $this->assertEquals(['01', 'SENDR1', 'RECVR1'], $parser->drop(3));
            $this->assertEquals('210616', $parser->peek());
        });
    }

    /**
     * @dataProvider headerWithDefaultedPhysicalRecordLengthFieldProducer
     */
    public function testDropCanIncludeADefaultedField(string|array $input): void
    {
        $this->withParser($input, function ($parser) {
            $this->assertEquals(
                ['01', 'SENDR1', 'RECVR1', '210616', '1700', '01', ''],
                $parser->drop(7)
            );
            $this->assertEquals('10', $parser->peek());
        });
    }

    /**
     * @dataProvider transactionInputProducer
     */
    public function testShiftTextReturnsTheRemainderOfBufferAsText(string|array $input): void
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
    public function testShiftOffDefaultedTextField(string|array $input): void
    {
        $this->withParser($input, function ($parser) {
            $parser->drop(21);
            $this->assertEquals('', $parser->shiftText());
        });
    }

    /**
     * @dataProvider transactionWithPaddedInputForNoPhysicalRecordLengthProducer
     */
    public function testConstructWithoutPhysicalRecordLengthCorrectlyHandlesPadding(string|array $input): void
    {
        $this->withParser($input, function ($parser) {
            $this->assertEquals(
                ['16', '003', '10000', 'D', '3', '1', '1000', '5', '10000', '30', '25000', '123456789', '987654321'],
                $parser->drop(13)
            );
            $this->assertEquals(
                'The following                                      '
                    . ' character is, of all the path separation                                    '
                    . " characters I've ever used, my absolute favorite: /                          ",
                $parser->shiftText()
            );
        });
    }

    /**
     * @dataProvider transactionWithPaddedInputProducer
     */
    public function testConstructWithPhysicalRecordLengthCorrectlyHandlesPadding(string|array $input): void
    {
        $this->withPhysicalRecordLengthParser($input, 80, function ($parser) {
            $this->assertEquals(
                ['16', '003', '10000', 'D', '3', '1', '1000', '5', '10000', '30', '25000', '123456789', '987654321'],
                $parser->drop(13)
            );
            $this->assertEquals(
                'The following character is, of all the path separation '
                    . "characters I've ever used, my absolute favorite: /",
                $parser->shiftText()
            );
        });
    }

    /**
     * @dataProvider transactionWithPaddedInputProducer
     */
    public function testSettingPhysicalRecordLengthCorrectlyHandlesPadding(string|array $input): void
    {
        $this->withParser($input, function ($parser) {
            $parser->setPhysicalRecordLength(80);
            $this->assertEquals(
                ['16', '003', '10000', 'D', '3', '1', '1000', '5', '10000', '30', '25000', '123456789', '987654321'],
                $parser->drop(13)
            );
            $this->assertEquals(
                'The following character is, of all the path separation '
                    . "characters I've ever used, my absolute favorite: /",
                $parser->shiftText()
            );
        });
    }

    /**
     * @dataProvider headerInputProducer
     */
    public function testThrowsIfPeekingPastEndOfLine(string|array $input): void
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
    public function testThrowsIfShiftingPastEndOfLine(string|array $input): void
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
    public function testThrowsIfDroppingMoreThanAvailableInBuffer(string|array $input): void
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
    public function testThrowsIfShiftingTextPastEndOfLine(string|array $input): void
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

    /**
     * @dataProvider transactionInputProducer
     */
    public function testThrowsIfAttemptingToPeekAfterReadingTextField(string|array $input): void
    {
        $this->withParser($input, function ($parser) {
            $parser->shiftText();

            $this->expectException(\Exception::class);
            $this->expectExceptionMessage('Cannot access fields at the end of the buffer.');
            $parser->peek();
        });
    }

    /**
     * @dataProvider transactionInputProducer
     */
    public function testThrowsIfAttemptingToShiftAfterReadingTextField(string|array $input): void
    {
        $this->withParser($input, function ($parser) {
            $parser->shiftText();

            $this->expectException(\Exception::class);
            $this->expectExceptionMessage('Cannot access fields at the end of the buffer.');
            $parser->shift();
        });
    }

    /**
     * @dataProvider transactionInputProducer
     */
    public function testThrowsIfAttemptingToDropAfterReadingTextField(string|array $input): void
    {
        $this->withParser($input, function ($parser) {
            $parser->shiftText();

            $this->expectException(\Exception::class);
            $this->expectExceptionMessage('Cannot access fields at the end of the buffer.');
            $parser->drop(2);
        });
    }

    /**
     * @dataProvider headerInputWithUnterminatedVersionFieldProducer
     */
    public function testThrowsIfPeekingAtAnUnterminatedField(string|array $input): void
    {
        $this->withParser($input, function ($parser) {
            $parser->drop(8);

            $this->expectException(\Exception::class);
            $this->expectExceptionMessage('Cannot access last (non-text) field on unterminated input line.');
            $parser->peek();
        });
    }

    /**
     * @dataProvider headerInputWithUnterminatedVersionFieldProducer
     */
    public function testThrowsIfShiftingAnUnterminatedField(string|array $input): void
    {
        $this->withParser($input, function ($parser) {
            $parser->drop(8);

            $this->expectException(\Exception::class);
            $this->expectExceptionMessage('Cannot access last (non-text) field on unterminated input line.');
            $parser->shift();
        });
    }

    /**
     * @dataProvider headerInputWithUnterminatedVersionFieldProducer
     */
    public function testThrowsIfDroppingAnUnterminatedField(string|array $input): void
    {
        $this->withParser($input, function ($parser) {
            $this->expectException(\Exception::class);
            $this->expectExceptionMessage('Cannot access last (non-text) field on unterminated input line.');
            $parser->drop(9);
        });
    }

    public function testThrowsIfAttemptingToCallContinueWithNonContinuationRecord(): void
    {
        $this->withParser('01,SENDR1,RECVR1/', function ($parser) {
            $this->expectException(\Exception::class);
            $this->expectExceptionMessage('Cannot call ::continue() on non-continuation input.');
            $parser->continue('16,003,10000,D,3/');
        });
    }

   /*
    * Reading normal fields interlaced with ::continue() calls are OK; however,
    * when you call a text field, which must always be at the end, it means
    * you've finished reading in a record's raw lines and there is no good
    * reason to continue past that. Let's close that cans of worms before
    * they're opened (and make it harder to mess up usage at higher levels of
    * the code).
    */
    public function testThrowsIfAttemptingToCallContinueAfterReadingTextField(): void
    {
        $transactionRecordLine = "16,003,10000,D,3,1,1000,5,10000,30,25000,123456789,987654321/";
        $this->withParser($transactionRecordLine, function ($parser) {
            // Read regular fields before a continue? Go for it.
            $parser->drop(13);

            // Everything's hunky-dory so far...
            $parser->continue('88,The following character is, of all the path separation characters ');
            $parser->continue("88,I've ever used, my absolute favorite: ");
            $parser->continue('88,/');

            // ...and still good!
            $this->assertEquals(
                "The following character is, of all the path separation characters I've ever used, my absolute favorite: /",
                $parser->shiftText()
            );

            // YOU SHALL NOT PASS!
            $this->expectException(\Exception::class);
            $this->expectExceptionMessage('Cannot call ::continue() after reading the text field.');
            $parser->continue('88,Why would you even want to do this?');
        });
    }

    /**
     * @dataProvider transactionWithPaddedInputTooLongProducer
     */
    public function testThrowsWhenContructWithLineLongerThanPhysicalLength(string|array $input): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Input line length exceeds requested physical record length.');
        $this->withPhysicalRecordLengthParser($input, 80, function ($parser) {});
    }

    /**
     * @dataProvider transactionWithPaddedInputTooLongProducer
     */
    public function testThrowsWhenSetPhysicalRecordLengthExceedingOriginalLineLength(string|array $input): void
    {
        $this->withParser($input, function ($parser) {
            $this->expectException(\Exception::class);
            $this->expectExceptionMessage('Input line length exceeds requested physical record length.');
            $parser->setPhysicalRecordLength(80);
        });
    }

}
