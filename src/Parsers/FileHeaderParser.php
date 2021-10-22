<?php

namespace STS\Bai2\Parsers;

use STS\Bai2\Exceptions\InvalidTypeException;
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

    public function offsetGet(string $key): string|int|float|null
    {
        $this->rawFields ??= $this->parser->drop(9);
        return $this->parseField($key, $this->rawFields[self::index($key)]);
    }

    private static function index(string $key): int
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

    private function parseField(string $key, string $value): string|int|float|null
    {
        return match ($key) {
            'recordCode' => $this->parseRecordCode($value),
            'senderIdentification' => $this->parseSenderIdentification($value),
            'receiverIdentification' => $this->parseReceiverIdentification($value),
            'fileCreationDate' => $this->parseFileCreationDate($value),
            'fileCreationTime' => $this->parseFileCreationTime($value),
            'fileIdentificationNumber' => $this->parseFileIdentificationNumber($value),
            'physicalRecordLength' => $this->parsePhysicalRecordLength($value),
            'blockSize' => $this->parseBlockSize($value),
            'versionNumber' => $this->parseVersionNumber($value),
        };
    }

    private function parseRecordCode(string $value): string
    {
        if ($value === '') {
            throw new InvalidTypeException(
                'Invalid field type: "Record Code" cannot be omitted.'
            );
        } else if ($value !== '01') {
            throw new InvalidTypeException(
                'Invalid field type: "Record Code" must be "01".'
            );
        }

        return $value;
    }

    private function parseSenderIdentification(string $value): string
    {
        if (preg_match('/^[[:alnum:]]+$/', $value) === 1) {
            return $value;
        } else if ($value === '') {
            throw new InvalidTypeException(
                'Invalid field type: "Sender Identification" cannot be omitted.'
            );
        }

        throw new InvalidTypeException(
            'Invalid field type: "Sender Identification" must be alpha-numeric.'
        );
    }

    private function parseReceiverIdentification(string $value): string
    {
        if (preg_match('/^[[:alnum:]]+$/', $value) === 1) {
            return $value;
        } else if ($value === '') {
            throw new InvalidTypeException(
                'Invalid field type: "Receiver Identification" cannot be omitted.'
            );
        }

        throw new InvalidTypeException(
            'Invalid field type: "Receiver Identification" must be alpha-numeric.'
        );
    }

    private function parseFileCreationDate(string $value): string
    {
        if (preg_match('/^\d{6}$/', $value) === 1) {
            return $value;
        } else if ($value === '') {
            throw new InvalidTypeException(
                'Invalid field type: "File Creation Date" cannot be omitted.'
            );
        }

        throw new InvalidTypeException(
            'Invalid field type: "File Creation Date" must be composed of exactly 6 numerals.'
        );
    }

    private function parseFileCreationTime(string $value): string
    {
        if (preg_match('/^\d{4}$/', $value) === 1) {
            return $value;
        } else if ($value === '') {
            throw new InvalidTypeException(
                'Invalid field type: "File Creation Time" cannot be omitted.'
            );
        }

        throw new InvalidTypeException(
            'Invalid field type: "File Creation Time" must be composed of exactly 4 numerals.'
        );
    }

    private function parseFileIdentificationNumber(string $value): string
    {
        if (preg_match('/^\d+$/', $value) === 1) {
            return $value;
        } else if ($value === '') {
            throw new InvalidTypeException(
                'Invalid field type: "File Identification Number" cannot be omitted.'
            );
        }

        throw new InvalidTypeException(
            'Invalid field type: "File Identification Number" must be composed of 1 or more numerals.'
        );
    }

    private function parsePhysicalRecordLength(string $value): ?int
    {
        if (preg_match('/^\d+$/', $value) === 1) {
            return (int) $value;
        } else if ($value === '') {
            return null;
        }

        throw new InvalidTypeException(
            'Invalid field type: "Physical Record Length", if provided, must be composed of 1 or more numerals.'
        );
    }

    private function parseBlockSize(string $value): ?int
    {
        if (preg_match('/^\d+$/', $value) === 1) {
            return (int) $value;
        } else if ($value === '') {
            return null;
        }

        throw new InvalidTypeException(
            'Invalid field type: "Block Size", if provided, must be composed of 1 or more numerals.'
        );
    }

    private function parseVersionNumber(string $value): string
    {
        if ($value === '') {
            throw new InvalidTypeException(
                'Invalid field type: "Version Number" cannot be omitted.'
            );
        } else if ($value !== '2') {
            throw new InvalidTypeException(
                'Invalid field type: "Version Number" must be "2" (this library only supports v2 of the BAI format).'
            );
        }

        return $value;
    }

}
