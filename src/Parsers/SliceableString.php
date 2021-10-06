<?php

namespace STS\Bai2\Parsers;

class SliceableString
{

    public function __construct(protected string $raw)
    {
    }

    public function slice(int $begin, ?int $end = null): string
    {
        if (is_null($end)) {
            return substr($this->raw, $begin);
        }

        // TODO(zmd): decide if we want to allow negative slice indices, etc.;
        //   if so, make sure we're doing the right thing; if not, raise when
        //   non-positive arguments given or when end is not greater than or
        //   equal to begin.
        $offset = $end - $begin;
        return substr($this->raw, $begin, $offset);
    }

    public function pos(string $needle, int $offset = 0): int|bool
    {
        return strpos($this->raw, $needle, $offset);
    }

    public function len(): int
    {
        return strlen($this->raw);
    }

}
