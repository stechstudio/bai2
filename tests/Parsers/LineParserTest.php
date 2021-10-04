<?php

namespace STS\Bai2\Parsers;

use PHPUnit\Framework\TestCase;

final class LineParserTest extends TestCase
{

    private static string $headerLine = '01,SENDR1,RECVR1,210616,1700,01,80,10,2/';

    private static string $transactionLine = "16,003,10000,D,3,1,1000,5,10000,30,25000,123456789,987654321,The following character is, of all the path separation characters I've ever used, my absolute favorite: /";

    private static string $transactionLineDefaultedText = "16,003,10000,D,3,1,1000,5,10000,30,25000,123456789,987654321,/";

    public function testPeekReturnsNextFieldWithoutConsumingIt()
    {
        $parser = new LineParser(self::$headerLine);
        $this->assertEquals('01', $parser->peek());
        $this->assertEquals('01', $parser->peek());
    }

    public function testTakeWithNoArgumentsReturnsNextFieldAndConsumesIt()
    {
        $parser = new LineParser(self::$headerLine);
        $this->assertEquals('01', $parser->take());
        $this->assertEquals('SENDR1', $parser->peek());
    }

    public function testTakeOneReturnsNextFieldAndConsumesIt()
    {
        $parser = new LineParser(self::$headerLine);
        $this->assertEquals('01', $parser->takeOne());
        $this->assertEquals('SENDR1', $parser->peek());
    }

    public function testTakeWithArgReturnsNumFieldsRequestedAndConsumesThem()
    {
        $parser = new LineParser(self::$headerLine);
        $this->assertEquals(['01', 'SENDR1', 'RECVR1'], $parser->take(3));
        $this->assertEquals('210616', $parser->peek());
    }

    public function testTakeNReturnsNumFieldsRequestedAndConsumesThem()
    {
        $parser = new LineParser(self::$headerLine);
        $this->assertEquals(['01', 'SENDR1', 'RECVR1'], $parser->takeN(3));
        $this->assertEquals('210616', $parser->peek());
    }

    public function testTakeTextReturnsTheRemainderOfBufferAsText()
    {
        $parser = new LineParser(self::$transactionLine);

        // consume and discard the non-text fields
        $parser->take(13);

        // Note: per the spec, text fields are always at the end of a line, and
        // you have to at least partially parse a line first to know at what
        // position that text field begins.
        $this->assertEquals(
            "The following character is, of all the path separation characters I've ever used, my absolute favorite: /",
            $parser->takeText()
        );
    }

    public function testTakeTextReturnsEmptyStringForDefaultedTextField()
    {
        $parser = new LineParser(self::$transactionLineDefaultedText);

        // consume and discard the non-text fields
        $parser->take(13);

        // Note: per the spec, text fields which are defaulted are denominated
        // with ,/
        $this->assertEquals('', $parser->takeText());
    }

}
