<?php

namespace STS\Bai2\Parsers;

class FileTrailerParser
{

    public function push(string $line): self
    {
        // TODO(zmd): implement me for real
        return $this;
    }

    public function offsetGet(string $key): string|int|float|null
    {
        // TODO(zmd): implement me for real
        return match ($key) {
            'recordCode'       => '99',
            'fileControlTotal' => 15000,
            'numberOfGroups'   => 10,
            'numberOfRecords'  => 42,
        };
    }

}
