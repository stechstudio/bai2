<?php

namespace STS\Bai2\Parsers;

use STS\Bai2\Exceptions\InvalidFieldNameException;

class FileHeaderParser
{

    private ?array $rawFields;

    private MultilineParser $parser;

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
        return $this->parseField($key, $this->parse()[$this->index($key)]);
    }

    private function parse(): array
    {
        return $this->rawFields ??= $this->parser->drop(9);
    }

    private function index(string $key): int
    {
        try {
            return match ($key) {
                'recordCode' => 0,
                'senderIdentification' => 1,
                'receiverIdentification' => 2,
                'fileCreationDate' => 3,
                'fileCreationTime' => 4,
                'fileIdentificationNumber' => 5,
                'physicalRecordLength' => 6,
                'blockSize' => 7,
                'versionNumber' => 8,
            };
        } catch (\UnhandledMatchError) {
            throw new InvalidFieldNameException("File Header does not have a \"{$key}\" field.");
        }
    }

    private function parseField(string $key, string $value): string|int|null
    {
        return match ($key) {
            'recordCode' =>
                $this->validate($value, 'Record Code')
                     ->is('01', 'must be "01"')
                     ->string(),

            'senderIdentification' =>
                $this->validate($value, 'Sender Identification')
                     ->match('/^[[:alnum:]]+$/', 'must be alpha-numeric')
                     ->string(),

            'receiverIdentification' =>
                $this->validate($value, 'Receiver Identification')
                     ->match('/^[[:alnum:]]+$/', 'must be alpha-numeric')
                     ->string(),

            'fileCreationDate' =>
                $this->validate($value, 'File Creation Date')
                     ->match('/^\d{6}$/', 'must be composed of exactly 6 numerals')
                     ->string(),

            'fileCreationTime' =>
                $this->validate($value, 'File Creation Time')
                     ->match('/^\d{4}$/', 'must be composed of exactly 4 numerals')
                     ->string(),

            'fileIdentificationNumber' =>
                $this->validate($value, 'File Identification Number')
                     ->match('/^\d+$/', 'must be composed of 1 or more numerals')
                     ->int(),

            'physicalRecordLength' =>
                $this->validate($value, 'Physical Record Length')
                     ->match('/^\d+$/', 'must be composed of 1 or more numerals')
                     ->int(default: null),

            'blockSize' =>
                $this->validate($value, 'Block Size')
                     ->match('/^\d+$/', 'must be composed of 1 or more numerals')
                     ->int(default: null),

            'versionNumber' =>
                $this->validate($value, 'Version Number')
                     ->is('2', 'must be "2" (this library only supports v2 of the BAI format)')
                     ->string(),
        };
    }

    protected function validate(string $value, string $longName): FieldParser
    {
        return new FieldParser($value, $longName);
    }

}
