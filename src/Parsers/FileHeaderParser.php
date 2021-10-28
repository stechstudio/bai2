<?php

namespace STS\Bai2\Parsers;

use STS\Bai2\Exceptions\InvalidUseException;

final class FileHeaderParser extends AbstractRecordParser
{
    use RecordParserTrait;

    public function __construct(protected ?int $physicalRecordLength = null)
    {
        if (is_int($physicalRecordLength)) {
            throw new InvalidUseException(
                'It is an error to try to set the Physical Record Length on a '
                    . 'File Header before it has been parsed and read the '
                    . "File Header's content."
            );
        }
    }

    protected static function recordCode(): string
    {
        return '01';
    }

    protected function parseFields(): self
    {
        $this->parsed['recordCode'] = $this->shiftField();

        $this->parsed['senderIdentification'] =
            $this->shiftAndParseField('Sender Identification')
                 ->match('/^[[:alnum:]]+$/', 'must be alpha-numeric')
                 ->string();

        $this->parsed['receiverIdentification'] =
            $this->shiftAndParseField('Receiver Identification')
                 ->match('/^[[:alnum:]]+$/', 'must be alpha-numeric')
                 ->string();

        $this->parsed['fileCreationDate'] =
            $this->shiftAndParseField('File Creation Date')
                 ->match('/^\d{6}$/', 'must be composed of exactly 6 numerals')
                 ->string();

        $this->parsed['fileCreationTime'] =
            $this->shiftAndParseField('File Creation Time')
                 ->match('/^\d{4}$/', 'must be composed of exactly 4 numerals')
                 ->string();

        $this->parsed['fileIdentificationNumber'] =
            $this->shiftAndParseField('File Identification Number')
                 ->match('/^\d+$/', 'must be composed of 1 or more numerals')
                 ->int();

        $this->parsed['physicalRecordLength'] =
            $this->shiftAndParseField('Physical Record Length')
                 ->match('/^\d+$/', 'must be composed of 1 or more numerals')
                 ->int(default: null);
        $this->getParser()->setPhysicalRecordLength($this->parsed['physicalRecordLength']);

        $this->parsed['blockSize'] =
            $this->shiftAndParseField('Block Size')
                 ->match('/^\d+$/', 'must be composed of 1 or more numerals')
                 ->int(default: null);

        $this->parsed['versionNumber'] =
            $this->shiftAndParseField('Version Number')
                 ->is('2', 'must be "2" (this library only supports v2 of the BAI format)')
                 ->string();

        return $this;
    }

}
