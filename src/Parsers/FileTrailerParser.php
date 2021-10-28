<?php

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

        // TODO(zmd): finish implementing me!
        return $this;
    }

}
