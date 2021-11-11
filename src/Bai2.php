<?php

declare(strict_types=1);

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
        if ($file = fopen($pathname, 'r')) {
            return self::parseFromResource($file);
        }

        throw new \RuntimeException('Error: unable to open given file for read with fopen().');
    }

    public static function parseFromResource($fileStream): FileRecord
    {
        $fileRecord = new FileRecord();

        try {
            while ($line = fgets($fileStream)) {
                // trim off \r and \n (but not other whitespace that may be
                // legit part of a record)
                if ($line = trim($line, "\r\n")) {
                    $fileRecord->parseLine($line);
                }
            }

            if (!feof($fileStream)) {
                throw new \RuntimeException('Error: unable to finish reading input file with fgets().');
            }
        } finally {
            fclose($fileStream);
        }

        return $fileRecord;
    }

    public static function recordTypeCode(string $line): string
    {
        return substr($line, 0, 2);
    }

}
