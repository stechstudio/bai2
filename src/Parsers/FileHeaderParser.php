<?php

namespace STS\Bai2\Parsers;

use STS\Bai2\Exceptions\ExtantAssertionException;

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
        return $this->rawFields[self::index($key)];
    }

    private static function index(string $key): int
    {
        $index = array_search($key, self::$fields);
        if ($index === false) {
            // TODO(zmd): define and use package-specific exception
            throw new \RuntimeException('Unknown field.');
        }

        return $index;
    }

}
