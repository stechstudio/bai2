<?php

declare(strict_types=1);

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
        $this->constraint = function () use ($constraint, $violationMessage) {
            if ($this->value !== $constraint) {
                $this->throw(" {$violationMessage}.");
            }
        };

        return $this;
    }

    public function match(string $constraint, string $violationMessage): self
    {
        $this->constraint = function () use ($constraint, $violationMessage) {
            if (preg_match($constraint, $this->value) !== 1) {
                $this->throw(" {$violationMessage}.");
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

    protected function parseValue(array $options, callable $caster): string|int|null
    {
        if ($this->value === '') {
            return $this->getDefaultOrThrow($options);
        } else if ($this->constraint) {
            ($this->constraint)();
        }

        return $caster();
    }

    protected function getDefaultOrThrow(array $options): string|int|null
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
