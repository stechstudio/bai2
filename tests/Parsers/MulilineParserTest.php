<?php

namespace STS\Bai2\Parsers;

use PHPUnit\Framework\TestCase;

final class MultilineParserTest extends TestCase
{

    public function testPeekReturnsNextFieldWithoutConsumingIt(): void
    {
        $parser = new MultilineParser('01,SENDR1,RECVR1,210616,1700,01,80,10,2/');
        $this->assertEquals('01', $parser->peek());
        $this->assertEquals('01', $parser->peek());
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
        $parser = new MultilineParser('01,SENDR1,RECVR1/');
        $parser->continue('88,210616,1700,01,80,10,2/');
        $parser->drop(3);

        $this->assertEquals('210616', $parser->peek());
        $this->assertEquals('210616', $parser->peek());
    }

}
