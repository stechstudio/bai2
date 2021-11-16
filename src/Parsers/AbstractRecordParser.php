<?php

declare(strict_types=1);

namespace STS\Bai2\Parsers;

abstract class AbstractRecordParser implements \ArrayAccess
{

    abstract public function pushLine(string $line): self;

    abstract public function toArray(): array;

}
