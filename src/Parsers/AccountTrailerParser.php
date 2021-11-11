<?php

declare(strict_types=1);

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

        $this->parsed['accountControlTotal'] =
            $this->shiftAndParseField('Account Control Total')
                 ->match('/^[-+]?\d+$/', 'must be signed or unsigned integer')
                 ->int();

        $this->parsed['numberOfRecords'] =
            $this->shiftAndParseField('Number of Records')
                 ->match('/^\d+$/', 'should be unsigned integer')
                 ->int();

        return $this;
    }

}
