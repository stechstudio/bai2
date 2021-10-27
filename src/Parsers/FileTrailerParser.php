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
        $this->parsed['recordCode'] = $this->getParser()->shift();

        // TODO(zmd): finish implementing me!
        return $this;
    }

}
