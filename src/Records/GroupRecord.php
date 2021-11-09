<?php

namespace STS\Bai2\Records;

use STS\Bai2\Bai2;

use STS\Bai2\Parsers\GroupHeaderParser;
use STS\Bai2\Parsers\GroupTrailerParser;

class GroupRecord
{

    public function __construct(protected int $physicalRecordLength)
    {
    }

    public function parseLine(string $line): void
    {
        // TODO(zmd): finish implementing me
        match ($recordCode = Bai2::recordTypeCode($line)) {
            '02' => $this->processHeader($recordCode, $line),
            '88' => $this->processContinuation($line),
            '98' => $this->processTrailer($recordCode, $line),
            default => $this->processChildRecord($recordCode, $line)
        };
    }

    protected function processHeader(string $recordCode, string $line): void
    {
        // TODO(zmd): finish implementing me
        $this->headerParser = new GroupHeaderParser(
            physicalRecordLength: $this->physicalRecordLength,
        );
        $this->headerParser->pushLine($line);
    }

    protected function processTrailer(string $recordCode, string $line): void
    {
        // TODO(zmd): finish implementing me
        $this->trailerParser = new GroupTrailerParser(
            physicalRecordLength: $this->physicalRecordLength,
        );
        $this->trailerParser->pushLine($line);
    }

    protected function processContinuation(string $line): void
    {
        // TODO(zmd): implement me
    }

    protected function processChildRecord(string $recordCode, string $line): void
    {
        // TODO(zmd): implement me
    }

}
