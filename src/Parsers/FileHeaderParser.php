<?php

namespace STS\Bai2\Parsers;

use STS\Bai2\Exceptions\InvalidFieldNameException;

class FileHeaderParser
{

    protected MultilineParser $parser;

    protected array $parsed = [];

    public function push(string $line): self
    {
        if (!isset($this->parser)) {
            $this->parser = new MultilineParser($line);
        } else {
            $this->parser->continue($line);
        }

        return $this;
    }

    public function offsetGet(string $key): string|int|null
    {
        if (array_key_exists($key, $this->parseAll()->parsed)) {
            return $this->parsed[$key];
        } else {
            throw new InvalidFieldNameException(
                "File Header does not have a \"{$key}\" field."
            );
        }
    }

    protected function parse(string $value, string $key): FieldParser
    {
        return new FieldParser($value, $this->keyToFullName($key));
    }

    protected function keyToFullName(string $key): string
    {
        // "fooBarBaz" -> ['F', 'oo', 'B', 'ar', 'B', 'az']
        $components = preg_split(
            '/([A-Z])/',
            ucfirst($key),
            flags: PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY
        );

        // ['F', 'oo', 'B', 'ar', 'B', 'az'] -> [['F', 'oo'], ['B', 'ar'], ['B', 'az']]
        $chunked = array_chunk($components, 2);

        // [['F', 'oo'], ['B', 'ar'], ['B', 'az']] -> ['Foo', 'Bar', 'Baz']
        $words = array_map(fn ($chunk) => implode('', $chunk), $chunked);

        // ['Foo', 'Bar', 'Baz'] -> 'Foo Bar Baz'
        return implode(' ', $words);
    }

    // ------------------------------------------------------------------------

    private function parseAll(): self
    {
        if (empty($this->parsed)) {
            $this->parsed['recordCode'] =
                $this->parse($this->parser->shift(), 'recordCode')
                     ->is('01', 'must be "01"')
                     ->string();

            $this->parsed['senderIdentification'] =
                $this->parse($this->parser->shift(), 'senderIdentification')
                     ->match('/^[[:alnum:]]+$/', 'must be alpha-numeric')
                     ->string();

            $this->parsed['receiverIdentification'] =
                $this->parse($this->parser->shift(), 'receiverIdentification')
                     ->match('/^[[:alnum:]]+$/', 'must be alpha-numeric')
                     ->string();

            $this->parsed['fileCreationDate'] =
                $this->parse($this->parser->shift(), 'fileCreationDate')
                     ->match('/^\d{6}$/', 'must be composed of exactly 6 numerals')
                     ->string();

            $this->parsed['fileCreationTime'] =
                $this->parse($this->parser->shift(), 'fileCreationTime')
                     ->match('/^\d{4}$/', 'must be composed of exactly 4 numerals')
                     ->string();

            $this->parsed['fileIdentificationNumber'] =
                $this->parse($this->parser->shift(), 'fileIdentificationNumber')
                     ->match('/^\d+$/', 'must be composed of 1 or more numerals')
                     ->int();

            $this->parsed['physicalRecordLength'] =
                $this->parse($this->parser->shift(), 'physicalRecordLength')
                     ->match('/^\d+$/', 'must be composed of 1 or more numerals')
                     ->int(default: null);

            $this->parsed['blockSize'] =
                $this->parse($this->parser->shift(), 'blockSize')
                     ->match('/^\d+$/', 'must be composed of 1 or more numerals')
                     ->int(default: null);

            $this->parsed['versionNumber'] =
                $this->parse($this->parser->shift(), 'versionNumber')
                     ->is('2', 'must be "2" (this library only supports v2 of the BAI format)')
                     ->string();
        }

        return $this;
    }

}
