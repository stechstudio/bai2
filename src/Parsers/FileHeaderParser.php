<?php

namespace STS\Bai2\Parsers;

use STS\Bai2\Exceptions\InvalidFieldNameException;
use STS\Bai2\Exceptions\InvalidRecordException;
use STS\Bai2\Exceptions\InvalidUseException;
use STS\Bai2\Exceptions\ParseException;

class FileHeaderParser
{

    private MultilineParser $parser;

    protected array $parsed = [];

    public function push(string $line): self
    {
        if (!isset($this->parser)) {
            $this->pushRecord($line);
        } else {
            $this->pushContinuation($line);
        }

        return $this;
    }

    public function offsetGet(string $key): string|int|null
    {
        if ($this->offsetExists($key)) {
            return $this->parsed[$key];
        } else {
            throw new InvalidFieldNameException(
                "{$this->readableParserName()} does not have a \"{$key}\" field."
            );
        }
    }

    public function offsetExists(string $key): bool
    {
        return array_key_exists($key, $this->parseAllOnce()->parsed);
    }

    protected function pushRecord(string $line): void
    {
        $this->parser = new MultilineParser($line);

        try {
            if ($this->parser->peek() != self::$recordCode) {
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

    protected function pushContinuation(string $line): void
    {
        try {
            $this->getParser()->continue($line);
        } catch (ParseException | InvalidUseException) {
            throw new InvalidRecordException(
                "Encountered an invalid or malformed {$this->readableParserName()} continuation."
            );
        }
    }

    protected function readableParserName(): string
    {
        $nameComponents = explode('\\', self::class);
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

    protected function parse(string $value, string $longName): FieldParser
    {
        return new FieldParser($value, $longName);
    }

    protected function getParser(): MultilineParser
    {
        try {
            return $this->parser;
        } catch (\Error) {
            throw new InvalidUseException("Cannot parse {$this->readableParserName()} without first pushing line(s).");
        }
    }

    private function parseAllOnce(): self
    {
        if (empty($this->parsed)) {
            // NOTE: the recordCode was pre-validated by this point, and must
            // always exist
            $this->parsed['recordCode'] = $this->getParser()->shift();

            $this->parseAll();
        }

        return $this;
    }

    // -------------------------------------------------------------------------

    protected static string $recordCode = '01';

    protected function parseAll(): self
    {
        $this->parsed['senderIdentification'] =
            $this->parse($this->getParser()->shift(), 'Sender Identification')
                 ->match('/^[[:alnum:]]+$/', 'must be alpha-numeric')
                 ->string();

        $this->parsed['receiverIdentification'] =
            $this->parse($this->getParser()->shift(), 'Receiver Identification')
                 ->match('/^[[:alnum:]]+$/', 'must be alpha-numeric')
                 ->string();

        $this->parsed['fileCreationDate'] =
            $this->parse($this->getParser()->shift(), 'File Creation Date')
                 ->match('/^\d{6}$/', 'must be composed of exactly 6 numerals')
                 ->string();

        $this->parsed['fileCreationTime'] =
            $this->parse($this->getParser()->shift(), 'File Creation Time')
                 ->match('/^\d{4}$/', 'must be composed of exactly 4 numerals')
                 ->string();

        $this->parsed['fileIdentificationNumber'] =
            $this->parse($this->getParser()->shift(), 'File Identification Number')
                 ->match('/^\d+$/', 'must be composed of 1 or more numerals')
                 ->int();

        $this->parsed['physicalRecordLength'] =
            $this->parse($this->getParser()->shift(), 'Physical Record Length')
                 ->match('/^\d+$/', 'must be composed of 1 or more numerals')
                 ->int(default: null);
        $this->getParser()->setPhysicalRecordLength($this->parsed['physicalRecordLength']);

        $this->parsed['blockSize'] =
            $this->parse($this->getParser()->shift(), 'Block Size')
                 ->match('/^\d+$/', 'must be composed of 1 or more numerals')
                 ->int(default: null);

        $this->parsed['versionNumber'] =
            $this->parse($this->getParser()->shift(), 'Version Number')
                 ->is('2', 'must be "2" (this library only supports v2 of the BAI format)')
                 ->string();

        return $this;
    }

}
