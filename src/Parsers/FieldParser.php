<?php

namespace STS\Bai2\Parsers;

use STS\Bai2\Exceptions\InvalidTypeException;

class FieldParser
{

    protected $constraint = null;

    public function __construct(protected string $value, protected $fullName)
    {
    }

    public function is(string $constraint, string $violationMessage): self
    {
        $this->constraint = function (bool $isRequired) use ($constraint, $violationMessage) {
            if ($this->value !== $constraint) {
                if ($isRequired) {
                    $this->throw(" {$violationMessage}.");
                } else {
                    $this->throw(", if provided, {$violationMessage}.");
                }
            }
        };

        return $this;
    }

    public function string(...$options): ?string
    {
        return $this->parseValue($options, fn () => (string) $this->value);
    }

    public function int(...$options): ?int
    {
        return $this->parseValue($options, fn () => (int) $this->value);
    }

    protected function parseValue(array $options, callable $caster): ?string
    {
        if ($this->value === '') {
            return $this->getDefaultOrElse($options);
        } else if ($this->constraint) {
            ($this->constraint)(isRequired: !$this->hasDefault($options));
        }

        return $caster();
    }

    protected function getDefaultOrElse(array $options): string|int|null
    {
        if ($this->hasDefault($options)) {
            return $options['default'];
        } else {
            $this->throw(' cannot be omitted.');
        }
    }

    protected function hasDefault(array $options): bool
    {
        return array_key_exists('default', $options);
    }

    protected function throw(string $message): void
    {
        throw new InvalidTypeException(
            "Invalid field type: \"{$this->fullName}\"{$message}"
        );
    }

}
