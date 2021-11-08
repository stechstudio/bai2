<?php

namespace STS\Bai2\Parsers;

final class FileTrailerParser extends AbstractRecordParser
{
    use RecordParserTrait;

    public function __construct(protected ?int $physicalRecordLength = null)
    {
    }

    protected static function recordCode(): string
    {
        return '99';
    }

    protected function parseFields(): self
    {
        $this->parsed['recordCode'] = $this->shiftField();

        $this->parsed['fileControlTotal'] =
            $this->shiftAndParseField('File Control Total')
                 ->match('/^[-+]?\d+$/', 'must be signed or unsigned integer')
                 ->int();

        $this->parsed['numberOfGroups'] =
            $this->shiftAndParseField('Number of Groups')
                 ->match('/^\d+$/', 'should be unsigned integer')
                 ->int();

        // TODO(zmd): validate format & default/optional
        $this->parsed['numberOfRecords'] =
            $this->shiftAndParseField('Number of Records')
                 ->int(default: null);

        return $this;
    }

}
