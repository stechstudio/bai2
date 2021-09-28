<?php
namespace STS\Bai2\RecordTypes;

use PHPUnit\Framework\TestCase;

final class FileRecordTypeTest extends TestCase
{

    private static string $line = '01,SENDR1,RECVR1,210616,1700,80,10,2/';

    public function testParseLineSetsCorrectRecordCode()
    {
        $fileRecord = new FileRecordType;
        $fileRecord->parseLine(self::$line);

        $this->assertEquals('01', $fileRecord->getRecordCode());
    }

}
