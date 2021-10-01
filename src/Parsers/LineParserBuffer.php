<?php

namespace STS\Bai2\Parsers;

use STS\Bai2\LineParser as OldLineParser;

// TODO(zmd): merge with old LineParser and move this to where it belongs...
//   (final will be LineParserBuffer)
class LineParserBuffer extends OldLineParser
{

    protected bool $valid = false;

    public function next(): ?string
    {
        if ($this->isEndOfLine()) {
            $this->valid = false;
            return null;
        } else {
            $this->valid = true;
            return $this->shift();
        }
    }

    public function valid(): bool
    {
        return $this->valid;
    }

}
