<?php

namespace STS\Bai2\Parsers;

use STS\Bai2\Exceptions\InvalidTypeException;

class FieldParser
{

    public function __construct(protected string $value, protected $fullName)
    {
    }

    public function string(...$options): ?string
    {
        if ($this->value === '') {
            if (array_key_exists('default', $options)) {
                return $options['default'];
            } else {
                throw new InvalidTypeException(
                    'Invalid field type: "' . $this->fullName .'" cannot be omitted.'
                );
            }
        }

        return (string) $this->value;
    }

}
