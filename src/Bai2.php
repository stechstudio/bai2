<?php
namespace STS\Bai2;

use STS\Bai2\Records\FileRecord;

class Bai2
{

    public static function parse(mixed $file): FileRecord
    {
        if (gettype($file) == 'string') {
            $file = fopen($file, 'r');
        }

        $fileRecord = new FileRecord;
        while ($line = trim(fgets($file))) {
            $fileRecord->parseLine($line);
        }

        fclose($file);

        return $fileRecord;
    }

    public static function recordTypeCode(string $line): string
    {
        return substr($line, 0, 2);
    }

}
