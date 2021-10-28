<?php

namespace STS\Bai2\Parsers;

use STS\Bai2\ReadableNameTrait;

use STS\Bai2\Exceptions\InvalidUseException;
use STS\Bai2\Exceptions\InvalidRecordException;
use STS\Bai2\Exceptions\ParseException;

trait RecordParserTrait
{
    use ReadableNameTrait, RecordParserIdempotentParsingTrait, RecordParserArrayAccessTrait;

    private MultilineParser $multilineParser;

    abstract protected static function recordCode(): string;

    protected ?int $physicalRecordLength;

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

    protected function shiftField(): string
    {
        return $this->getParser()->shift();
    }

    protected function getParser(): MultilineParser
    {
        try {
            return $this->multilineParser;
        } catch (\Error) {
            throw new InvalidUseException("Cannot parse {$this->readableClassName()} without first pushing line(s).");
        }
    }

    private function pushRecord(string $line): void
    {
        $this->multilineParser = new MultilineParser($line, $this->physicalRecordLength);

        try {
            if ($this->multilineParser->peek() != static::recordCode()) {
                  throw new InvalidRecordException(
                      "Encountered an invalid or malformed {$this->readableClassName()} record."
                  );
            }
        } catch (ParseException) {
            throw new InvalidRecordException (
                "Encountered an invalid or malformed {$this->readableClassName()} record."
            );
        }
    }

    private function pushContinuation(string $line): void
    {
        try {
            $this->getParser()->continue($line);
        } catch (ParseException | InvalidUseException) {
            throw new InvalidRecordException(
                "Encountered an invalid or malformed {$this->readableClassName()} continuation."
            );
        }
    }

}
