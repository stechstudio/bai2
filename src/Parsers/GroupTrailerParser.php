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
        $unsignedIntConstraint = ['/^\d+$/', 'should be unsigned integer'];

        $this->parsed['groupControlTotal'] =
            $this->shiftAndParseField('Group Control Total')
                 ->match('/^[-+]?\d+$/', 'must be signed or unsigned integer')
                 ->int();

        $this->parsed['numberOfAccounts'] =
            $this->shiftAndParseField('Number of Accounts')
                 ->match(...$unsignedIntConstraint)
                 ->int();
        var_dump($this->parsed['numberOfAccounts']);

        $this->parsed['numberOfRecords'] =
            $this->shiftAndParseField('Number of Records')
                 ->match(...$unsignedIntConstraint)
                 ->int();

        return $this;
    }

}
