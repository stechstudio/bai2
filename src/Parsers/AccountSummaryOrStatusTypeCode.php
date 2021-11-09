<?php

namespace STS\Bai2\Parsers;

// NOTE: we can switch to an enum once available (PHP 8.1+)
class AccountSummaryOrStatusTypeCode
{

    public const DEFAULTED = 0;
    public const STATUS = 1;
    public const SUMMARY = 2;
    public const INVALID = -1;

    public static function detect(?string $typeCode): int
    {
        if (is_null($typeCode)) {
            return self::DEFAULTED;
        }

        $typeCodeInt = (int) $typeCode;
        if (
            ($typeCodeInt >=   1 && $typeCodeInt <=  99) ||
            ($typeCodeInt >= 900 && $typeCodeInt <= 919)
        ) {
            return self::STATUS;
        } else if (
            ($typeCodeInt >= 100 && $typeCodeInt <= 799) ||
            ($typeCodeInt >= 920 && $typeCodeInt <= 999)
        ) {
            return self::SUMMARY;
        } else {
            return self::INVALID;
        }
    }

}
