<?php

namespace STS\Bai2\Parsers;

final class AccountTrailerParser extends AbstractRecordParser
{
    use RecordParserTrait;

    public function __construct(protected ?int $physicalRecordLength = null)
    {
    }

    protected static function recordCode(): string
    {
        return '49';
    }

    protected function parseFields(): self
    {
        $this->parsed['recordCode'] = $this->shiftField();

        // TODO(zmd): validate format & default/optional
        $this->parsed['accountControlTotal'] =
            $this->shiftAndParseField('Account Control Total')
                 ->int(default: null);

        // TODO(zmd): validate format & default/optional
        $this->parsed['numberOfRecords'] =
            $this->shiftAndParseField('Number of Records')
                 ->int(default: null);

        return $this;
    }

}
