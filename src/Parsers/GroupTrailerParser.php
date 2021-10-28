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

        // TODO(zmd): finish implementing me!
        return $this;
    }

}
