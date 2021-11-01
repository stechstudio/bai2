<?php

namespace STS\Bai2\Parsers;

final class GroupTrailerParser extends AbstractRecordParser
{
    use RecordParserTrait;

    public function __construct(protected ?int $physicalRecordLength = null)
    {
    }

    protected static function recordCode(): string
    {
        return '98';
    }

    protected function parseFields(): self
    {
        $this->parsed['recordCode'] = $this->shiftField();

        // TODO(zmd): validate format & default/optional
        $this->parsed['groupControlTotal'] =
            $this->shiftAndParseField('Group Control Total')
                 ->int(default: null);

        // TODO(zmd): validate format & default/optional
        $this->parsed['numberOfAccounts'] =
            $this->shiftAndParseField('Number of Accounts')
                 ->int(default: null);

        // TODO(zmd): validate format & default/optional
        $this->parsed['numberOfRecords'] =
            $this->shiftAndParseField('Number of Records')
                 ->int(default: null);

        return $this;
    }

}
