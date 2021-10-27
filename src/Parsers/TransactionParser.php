<?php

namespace STS\Bai2\Parsers;

final class TransactionParser extends AbstractRecordParser
{
    use RecordParserTrait;

    protected static function recordCode(): string
    {
        return '16';
    }

    protected function parseFields(): self
    {
        $this->parsed['recordCode'] = $this->getParser()->shift();

        // TODO(zmd): finish implementing me!
        return $this;
    }

}
