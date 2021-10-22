<?php

namespace STS\Bai2\Parsers;

use STS\Bai2\Exceptions\InvalidTypeException;

class FieldParser
{

    protected array $constraints = [];

    public function __construct(protected string $value, protected $fullName)
    {
    }

    public function is(string $constraint, string $violationMessage): void
    {
        $this->constraints[] = function () use ($constraint, $violationMessage) {
            if ($this->value !== $constraint) {
                throw new InvalidTypeException(
                    'Invalid field type: "' . $this->fullName . '" ' . $violationMessage . '.'
                );
            }
        };
    }

    public function string(...$options): ?string
    {
        if ($this->value === '') {
            return $this->getDefaultOrElse($options);
        }

        foreach ($this->constraints as $constraint) {
            $constraint();
        }

        return (string) $this->value;
    }

    public function int(...$options): ?int
    {
        if ($this->value === '') {
            return $this->getDefaultOrElse($options);
        }

        foreach ($this->constraints as $constraint) {
            $constraint();
        }

        return (string) $this->value;
    }

    protected function getDefaultOrElse(array $options): string|int|null
    {
        if (array_key_exists('default', $options)) {
            return $options['default'];
        } else {
            throw new InvalidTypeException(
                'Invalid field type: "' . $this->fullName .'" cannot be omitted.'
            );
        }
    }

}
