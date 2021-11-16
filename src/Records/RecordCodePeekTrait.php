<?php

declare(strict_types=1);

namespace STS\Bai2\Records;

trait RecordCodePeekTrait
{

    protected static function recordTypeCode(string $line): string
    {
        return substr($line, 0, 2);
    }

}
