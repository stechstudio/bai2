<?php

namespace STS\Bai2\Parsers;

final class AccountHeaderParser extends AbstractRecordParser
{
    use RecordParserTrait;

    public function __construct(protected ?int $physicalRecordLength = null)
    {
    }

    protected static function recordCode(): string
    {
        return '03';
    }

    protected static function readableClassName(): string
    {
        return 'Account Identifier and Summary Status';
    }

    protected function parseFields(): self
    {
        $this->parsed['recordCode'] = $this->shiftField();

        // TODO(zmd): finish implementing me!
        return $this;
    }

}
