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

        $this->parsed['groupControlTotal'] =
            $this->shiftAndParseField('Group Control Total')
                 ->match('/^[-+]?\d+$/', 'must be signed or unsigned integer')
                 ->int();

        $this->parsed['numberOfAccounts'] =
            $this->shiftAndParseField('Number of Accounts')
                 ->match('/^\d+$/', 'should be unsigned integer')
                 ->int();
        var_dump($this->parsed['numberOfAccounts']);

        // TODO(zmd): validate format & default/optional
        $this->parsed['numberOfRecords'] =
            $this->shiftAndParseField('Number of Records')
                 ->int(default: null);

        return $this;
    }

}
