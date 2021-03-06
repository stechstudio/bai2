<?php

declare(strict_types=1);

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
        $unsignedIntConstraint = ['/^\d+$/', 'should be unsigned integer'];

        $this->parsed['fileControlTotal'] =
            $this->shiftAndParseField('File Control Total')
                 ->match('/^[-+]?\d+$/', 'must be signed or unsigned integer')
                 ->int();

        $this->parsed['numberOfGroups'] =
            $this->shiftAndParseField('Number of Groups')
                 ->match(...$unsignedIntConstraint)
                 ->int();

        $this->parsed['numberOfRecords'] =
            $this->shiftAndParseField('Number of Records')
                 ->match(...$unsignedIntConstraint)
                 ->int();

        return $this;
    }

}
