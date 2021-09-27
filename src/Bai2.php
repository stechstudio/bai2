<?php
namespace STS\Bai2;

use STS\Bai2\RecordTypes\FileRecordType;

class Bai2
{

    public static function parse(mixed $file): FileRecordType
    {
        if (gettype($file) == 'string') {
            $file = fopen($file, 'r');
        }

        $fileRecord = new FileRecordType;
        while ($line = trim(fgets($file))) {
            $fileRecord->parseLine($line);
        }

        fclose($file);

        return $fileRecord;
    }

}
