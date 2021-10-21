<?php

namespace STS\Bai2\Parsers;

class FileHeaderParser
{

    public function push(string $line): self
    {
        // TODO(zmd): implement me for real
        return $this;
    }

    public function offsetGet(string $key): string|int|float|null
    {
        // TODO(zmd): implement me for real
        return match ($key) {
            'recordCode'               => '01',
            'senderIdentification'     => 'Foo',
            'receiverIdentification'   => 'Bar',
            'fileCreationDate'         => '210909',
            'fileCreationTime'         => '0800',
            'fileIdentificationNumber' => 'abc',
            'physicalRecordLength'     => 80,
            'blockSize'                => null,
            'versionNumber'            => '2',
        };
    }

}
