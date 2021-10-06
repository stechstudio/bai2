<?php

namespace STS\Bai2\Parsers;

class SliceableString
{

    public function __construct(public string $line)
    {
    }

    public function slice(int $begin, ?int $end = null): string
    {
        if (is_null($end)) {
            return substr($this->line, $begin);
        }

        // TODO(zmd): decide if we want to allow negative slice indices, etc.;
        //   if so, make sure we're doing the right thing; if not, raise when
        //   non-positive arguments given or when end is not greater than or
        //   equal to begin.
        $offset = $end - $begin;
        return substr($this->line, $begin, $offset);
    }

}
