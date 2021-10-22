<?php

namespace STS\Bai2\Parsers;

use STS\Bai2\Exceptions\InvalidTypeException;

class FieldParser
{

    protected $constraint = null;

    public function __construct(protected string $value, protected $fullName)
    {
    }

    public function is(string $constraint, string $violationMessage): void
    {
        $this->constraint = function () use ($constraint, $violationMessage) {
            if ($this->value !== $constraint) {
                $this->throw(" {$violationMessage}.");
            }
        };
    }

    public function string(...$options): ?string
    {
        if ($this->value === '') {
            return $this->getDefaultOrElse($options);
        }

        if ($this->constraint) {
            ($this->constraint)();
        }

        return (string) $this->value;
    }

    public function int(...$options): ?int
    {
        if ($this->value === '') {
            return $this->getDefaultOrElse($options);
        }

        if ($this->constraint) {
            ($this->constraint)();
        }

        return (string) $this->value;
    }

    protected function getDefaultOrElse(array $options): string|int|null
    {
        if (array_key_exists('default', $options)) {
            return $options['default'];
        } else {
            $this->throw(' cannot be omitted.');
        }
    }

    protected function throw(string $message): void
    {
        throw new InvalidTypeException(
            "Invalid field type: \"{$this->fullName}\"{$message}"
        );
    }

}
