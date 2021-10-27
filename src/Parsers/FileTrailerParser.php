<?php

namespace STS\Bai2\Parsers;

final class FileTrailerParser extends AbstractRecordParser
{
    use RecordParserTrait;

    protected static function recordCode(): string
    {
        return '99';
    }

    protected function parseFields(): self
    {
        // NOTE: the recordCode was pre-validated by this point, and must
        // always exist, so we parse it first.
        $this->parsed['recordCode'] = $this->getParser()->shift();

        // TODO(zmd): finish implementing me!
        return $this;
    }

}
