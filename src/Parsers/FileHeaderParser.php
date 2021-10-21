<?php

namespace STS\Bai2\Parsers;

use STS\Bai2\Exceptions\ExtantAssertionException;

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
        return $this->rawFields[self::index($key)];
    }

    private static function index(string $key): int
    {
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
    }

}
