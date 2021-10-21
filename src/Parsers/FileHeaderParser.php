<?php

namespace STS\Bai2\Parsers;

use STS\Bai2\Exceptions\InvalidTypeException;

class FileHeaderParser
{

    private ?array $rawFields;

    private MultilineParser $parser;

    private static array $fields = [
        'recordCode',
        'senderIdentification',
        'receiverIdentification',
        'fileCreationDate',
        'fileCreationTime',
        'fileIdentificationNumber',
        'physicalRecordLength',
        'blockSize',
        'versionNumber',
    ];

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
        // TODO(zmd): I'm not sure I like using this better than just using
        //   match...
        $index = array_search($key, self::$fields);
        if ($index === false) {
            // TODO(zmd): define and use package-specific exception
            throw new \RuntimeException('Unknown field.');
        }

        return $index;
    }

    private function parseField(string $key, string $value): string|int|float|null
    {
        return match ($key) {
            'recordCode' => $this->parseRecordCode($value),
            'senderIdentification' => $this->parseSenderIdentification($value),

            // TODO(zmd): temporarily non-exhaustive match, remove this once
            //   all validations in place:
            default => $value,
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
        if ($value === '') {
            throw new InvalidTypeException(
                'Invalid field type: "Sender Identification" cannot be omitted.'
            );
        // TODO(zmd): is there something like :alnum:?
        // TODO(zmd): this will also ensure non-empty, we can collapse this
        //   conditional tree, and we must do so.
        } else if (preg_match('/[a-zA-Z0-9_-]+/', $value) !== 1) {
            throw new InvalidTypeException(
                'Invalid field type: "Sender Identification" must be alpha-numeric.'
            );
        }

        return $value;
    }

}
