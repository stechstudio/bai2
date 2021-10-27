<?php

namespace STS\Bai2\Parsers;

final class FileHeaderParser extends AbstractRecordParser
{
    use RecordParserTrait;

    protected static function recordCode(): string
    {
        return '01';
    }

    protected function parseFields(): self
    {
        $this->parsed['recordCode'] = $this->getParser()->shift();

        $this->parsed['senderIdentification'] =
            $this->parseField($this->getParser()->shift(), 'Sender Identification')
                 ->match('/^[[:alnum:]]+$/', 'must be alpha-numeric')
                 ->string();

        $this->parsed['receiverIdentification'] =
            $this->parseField($this->getParser()->shift(), 'Receiver Identification')
                 ->match('/^[[:alnum:]]+$/', 'must be alpha-numeric')
                 ->string();

        $this->parsed['fileCreationDate'] =
            $this->parseField($this->getParser()->shift(), 'File Creation Date')
                 ->match('/^\d{6}$/', 'must be composed of exactly 6 numerals')
                 ->string();

        $this->parsed['fileCreationTime'] =
            $this->parseField($this->getParser()->shift(), 'File Creation Time')
                 ->match('/^\d{4}$/', 'must be composed of exactly 4 numerals')
                 ->string();

        $this->parsed['fileIdentificationNumber'] =
            $this->parseField($this->getParser()->shift(), 'File Identification Number')
                 ->match('/^\d+$/', 'must be composed of 1 or more numerals')
                 ->int();

        $this->parsed['physicalRecordLength'] =
            $this->parseField($this->getParser()->shift(), 'Physical Record Length')
                 ->match('/^\d+$/', 'must be composed of 1 or more numerals')
                 ->int(default: null);
        $this->getParser()->setPhysicalRecordLength($this->parsed['physicalRecordLength']);

        $this->parsed['blockSize'] =
            $this->parseField($this->getParser()->shift(), 'Block Size')
                 ->match('/^\d+$/', 'must be composed of 1 or more numerals')
                 ->int(default: null);

        $this->parsed['versionNumber'] =
            $this->parseField($this->getParser()->shift(), 'Version Number')
                 ->is('2', 'must be "2" (this library only supports v2 of the BAI format)')
                 ->string();

        return $this;
    }

}
