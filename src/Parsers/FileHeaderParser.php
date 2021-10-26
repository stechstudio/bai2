<?php

namespace STS\Bai2\Parsers;

use STS\Bai2\Exceptions\InvalidFieldNameException;
use STS\Bai2\Exceptions\InvalidRecordException;
use STS\Bai2\Exceptions\InvalidUseException;
use STS\Bai2\Exceptions\ParseException;

class FileHeaderParser
{

    protected MultilineParser $parser;

    protected array $parsed = [];

    protected static string $recordCode = '01';

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

    public function push(string $line): self
    {
        if (!isset($this->parser)) {
            $this->parser = new MultilineParser($line);
            $this->validateRecordLine();
        } else {
            try {
                $this->parser->continue($line);
            } catch (ParseException | InvalidUseException) {
                throw $this->invalidContinuationException();
            }
        }

        return $this;
    }

    public function offsetGet(string $key): string|int|null
    {
        if (array_key_exists($key, $this->parseAll()->parsed)) {
            return $this->parsed[$key];
        } else {
            throw new InvalidFieldNameException(
                "{$this->readableParserName()} does not have a \"{$key}\" field."
            );
        }
    }

    protected function parse(string $value, string $longName): FieldParser
    {
        return new FieldParser($value, $longName);
    }

    protected function validateRecordLine(): void
    {
        try {
            if ($this->parser->peek() != self::$recordCode) {
                  throw $this->invalidRecordException();
            }
        } catch (ParseException) {
            throw $this->invalidRecordException();
        }
    }

    protected function invalidRecordException(): InvalidRecordException
    {
        return new InvalidRecordException(
            "Encountered an invalid or malformed {$this->readableParserName()} record."
        );
    }

    protected function invalidContinuationException(): InvalidRecordException
    {
        return new InvalidRecordException(
            "Encountered an invalid or malformed {$this->readableParserName()} continuation."
        );
    }

    private function parseAll(): self
    {
        // TODO(zmd): throw if we try to parse but no parser was set (meaning,
        //   no lines were first pushed)
        if (empty($this->parsed)) {
            // NOTE: the recordCode was pre-validated by this point
            $this->parsed['recordCode'] = $this->parser->shift();

            $this->parsed['senderIdentification'] =
                $this->parse($this->parser->shift(), 'Sender Identification')
                     ->match('/^[[:alnum:]]+$/', 'must be alpha-numeric')
                     ->string();

            $this->parsed['receiverIdentification'] =
                $this->parse($this->parser->shift(), 'Receiver Identification')
                     ->match('/^[[:alnum:]]+$/', 'must be alpha-numeric')
                     ->string();

            $this->parsed['fileCreationDate'] =
                $this->parse($this->parser->shift(), 'File Creation Date')
                     ->match('/^\d{6}$/', 'must be composed of exactly 6 numerals')
                     ->string();

            $this->parsed['fileCreationTime'] =
                $this->parse($this->parser->shift(), 'File Creation Time')
                     ->match('/^\d{4}$/', 'must be composed of exactly 4 numerals')
                     ->string();

            $this->parsed['fileIdentificationNumber'] =
                $this->parse($this->parser->shift(), 'File Identification Number')
                     ->match('/^\d+$/', 'must be composed of 1 or more numerals')
                     ->int();

            $this->parsed['physicalRecordLength'] =
                $this->parse($this->parser->shift(), 'Physical Record Length')
                     ->match('/^\d+$/', 'must be composed of 1 or more numerals')
                     ->int(default: null);
            $this->parser->setPhysicalRecordLength($this->parsed['physicalRecordLength']);

            $this->parsed['blockSize'] =
                $this->parse($this->parser->shift(), 'Block Size')
                     ->match('/^\d+$/', 'must be composed of 1 or more numerals')
                     ->int(default: null);

            $this->parsed['versionNumber'] =
                $this->parse($this->parser->shift(), 'Version Number')
                     ->is('2', 'must be "2" (this library only supports v2 of the BAI format)')
                     ->string();
        }

        return $this;
    }

}
