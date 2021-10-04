<?php

namespace STS\Bai2\Parsers;

use PHPUnit\Framework\TestCase;

final class LineParserTest extends TestCase
{

    private static string $headerLine = '01,SENDR1,RECVR1,210616,1700,01,80,10,2/';

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

}
