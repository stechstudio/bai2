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

        // TODO(zmd): validate format & default/optional
        $this->parsed['fileControlTotal'] =
            $this->shiftAndParseField('File Control Total')
                 ->int(default: null);

        // TODO(zmd): validate format & default/optional
        $this->parsed['numberOfGroups'] =
            $this->shiftAndParseField('Number of Groups')
                 ->int(default: null);

        // TODO(zmd): validate format & default/optional
        $this->parsed['numberOfRecords'] =
            $this->shiftAndParseField('Number of Records')
                 ->int(default: null);

        return $this;
    }

}
