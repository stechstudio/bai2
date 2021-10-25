<?php

namespace STS\Bai2\Parsers;

use STS\Bai2\Exceptions\InvalidFieldNameException;

class FileHeaderParser
{

    protected MultilineParser $parser;

    private array $rawFields = [];

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
        if (array_key_exists($key, $this->parse())) {
            //$this->parse()[$key]
            return $this->parseField($key, $this->parse()[$key]);
        } else {
            throw new InvalidFieldNameException(
                "File Header does not have a \"{$key}\" field."
            );
        }
    }

    protected function validate(string $value, string $longName): FieldParser
    {
        return new FieldParser($value, $longName);
    }

    // ------------------------------------------------------------------------

    private function parse(): array
    {
        if (empty($this->rawFields)) {
            $this->rawFields['recordCode'] = $this->parser->shift();
            $this->rawFields['senderIdentification'] = $this->parser->shift();
            $this->rawFields['receiverIdentification'] = $this->parser->shift();
            $this->rawFields['fileCreationDate'] = $this->parser->shift();
            $this->rawFields['fileCreationTime'] = $this->parser->shift();
            $this->rawFields['fileIdentificationNumber'] = $this->parser->shift();
            $this->rawFields['physicalRecordLength'] = $this->parser->shift();
            $this->rawFields['blockSize'] = $this->parser->shift();
            $this->rawFields['versionNumber'] = $this->parser->shift();
        }

        return $this->rawFields;
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

}
