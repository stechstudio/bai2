<?php

declare(strict_types=1);

namespace STS\Bai2\Parsers;

// NOTE: we can switch to an enum once available (PHP 8.1+)
class TransactionDetailTypeCode
{

    public const CREDIT = 1;
    public const DEBIT = 2;
    public const LOAN = 3;
    public const NON_MONETARY = 4;
    public const CUSTOM = 5;
    public const INVALID = -1;

    public static function detect(string $typeCode): int
    {
        $typeCodeInt = (int) $typeCode;
        if ($typeCodeInt >= 101 && $typeCodeInt <= 399) {
            return self::CREDIT;
        } else if ($typeCodeInt >= 401 && $typeCodeInt <= 699) {
            return self::DEBIT;
        } else if ($typeCodeInt >= 700 && $typeCodeInt <= 799) {
            return self::LOAN;
        } else if ($typeCodeInt == 890) {
            return self::NON_MONETARY;
        } else if ($typeCodeInt >= 900 && $typeCodeInt <= 999) {
            return self::CUSTOM;
        } else {
            return self::INVALID;
        }
    }

}
