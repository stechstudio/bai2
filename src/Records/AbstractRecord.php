<?php

declare(strict_types=1);

namespace STS\Bai2\Records;

abstract class AbstractRecord
{

    abstract public function parseLine(string $line): void;

    abstract public function toArray(): array;

}
