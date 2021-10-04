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

        // not consumed, so we can peek again getting the same result
        $this->assertEquals('01', $parser->peek());
    }

}
