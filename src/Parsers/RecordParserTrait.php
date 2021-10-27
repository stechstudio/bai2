<?php

namespace STS\Bai2\Parsers;

use STS\Bai2\Exceptions\InvalidUseException;
use STS\Bai2\Exceptions\InvalidFieldNameException;
use STS\Bai2\Exceptions\InvalidRecordException;
use STS\Bai2\Exceptions\ParseException;

trait RecordParserTrait
{

    protected array $parsed = [];

    private MultilineParser $multilineParser;

    abstract protected function parseFields(): self;

    abstract protected static function recordCode(): string;

    public function offsetGet(mixed $offset): string|int|null
    {
        if ($this->offsetExists($offset)) {
            return $this->parsed[$offset];
        } else {
            throw new InvalidFieldNameException(
                "{$this->readableParserName()} does not have a \"{$offset}\" field."
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

    public function offsetUnset(mixed $offset): mixed
    {
        throw new InvalidUseException('::offsetUnset() is unsupported.');
    }

    public function pushLine(string $line): self
    {
        if (!isset($this->multilineParser)) {
            $this->pushRecord($line);
        } else {
            $this->pushContinuation($line);
        }

        return $this;
    }

    public function toArray(): array
    {
        return $this->parseFieldsOnce()->parsed;
    }

    protected function parseField(string $value, string $longName): FieldParser
    {
        return new FieldParser($value, $longName);
    }

    protected function getParser(): MultilineParser
    {
        try {
            return $this->multilineParser;
        } catch (\Error) {
            throw new InvalidUseException("Cannot parse {$this->readableParserName()} without first pushing line(s).");
        }
    }

    private function readableParserName(): string
    {
        $nameComponents = explode('\\', static::class);
        $nameSansParser = preg_replace('/Parser$/', '', end($nameComponents));

        // "FooBarBaz" -> ['F', 'oo', 'B', 'ar', 'B', 'az']
        $components = preg_split(
            '/([A-Z])/',
            $nameSansParser,
            flags: PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY
        );

        // ['F', 'oo', 'B', 'ar', 'B', 'az'] -> [['F', 'oo'], ['B', 'ar'], ['B', 'az']]
        $chunked = array_chunk($components, 2);

        // [['F', 'oo'], ['B', 'ar'], ['B', 'az']] -> ['Foo', 'Bar', 'Baz']
        $words = array_map(fn ($chunk) => implode('', $chunk), $chunked);

        // ['Foo', 'Bar', 'Baz'] -> 'Foo Bar Baz'
        return implode(' ', $words);
    }

    private function pushRecord(string $line): void
    {
        $this->multilineParser = new MultilineParser($line);

        try {
            if ($this->multilineParser->peek() != static::recordCode()) {
                  throw new InvalidRecordException(
                      "Encountered an invalid or malformed {$this->readableParserName()} record."
                  );
            }
        } catch (ParseException) {
            throw new InvalidRecordException (
                "Encountered an invalid or malformed {$this->readableParserName()} record."
            );
        }
    }

    private function pushContinuation(string $line): void
    {
        try {
            $this->getParser()->continue($line);
        } catch (ParseException | InvalidUseException) {
            throw new InvalidRecordException(
                "Encountered an invalid or malformed {$this->readableParserName()} continuation."
            );
        }
    }

    private function parseFieldsOnce(): self
    {
        if (empty($this->parsed)) {
            $this->parseFields();
        }

        return $this;
    }

}
