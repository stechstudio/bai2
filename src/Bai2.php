<?php

namespace STS\Bai2;

use STS\Bai2\Records\FileRecord;

class Bai2
{

    public static function parse(mixed $file): FileRecord
    {
        if (is_string($file)) {
            return self::parseFromFile($file);
        }

        return self::parseFromResource($file);
    }

    public static function parseFromFile(string $pathname): FileRecord
    {
        $file = fopen($pathname, 'r');
        return self::parseFromResource($file);
    }

    public static function parseFromResource($fileStream): FileRecord
    {
        $fileRecord = new FileRecord();
        while ($line = trim(fgets($fileStream))) {
            $fileRecord->parseLine($line);
        }

        fclose($fileStream);

        return $fileRecord;
    }

    public static function recordTypeCode(string $line): string
    {
        return substr($line, 0, 2);
    }

}
