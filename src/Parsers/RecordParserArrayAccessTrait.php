<?php

declare(strict_types=1);

namespace STS\Bai2\Parsers;

use STS\Bai2\ReadableNameTrait;

use STS\Bai2\Exceptions\InvalidUseException;
use STS\Bai2\Exceptions\InvalidFieldNameException;

trait RecordParserArrayAccessTrait
{
    use ReadableNameTrait, RecordParserIdempotentParsingTrait;

    public function offsetGet(mixed $offset): array|string|int|null
    {
        if ($this->offsetExists($offset)) {
            return $this->parsed[$offset];
        } else {
            throw new InvalidFieldNameException(
                "{$this->readableClassName()} does not have a \"{$offset}\" field."
            );
        }
    }

    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->parseFieldsOnce()->parsed);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new InvalidUseException('::offsetSet() is unsupported.');
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new InvalidUseException('::offsetUnset() is unsupported.');
    }

}
