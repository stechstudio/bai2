<?php

namespace STS\Bai2\Records;

use STS\Bai2\Bai2;

use STS\Bai2\Exceptions\MalformedInputException;
use STS\Bai2\Exceptions\InvalidTypeException;
use STS\Bai2\Exceptions\ParseException;

class TransactionRecord
{

    protected TransactionParser $recordParser;

    public function __construct(protected ?int $physicalRecordLength)
    {
    }

    public function parseLine(string $line): void
    {
        match ($recordCode = Bai2::recordTypeCode($line)) {
            '16' => $this->processRecord($recordCode, $line),
            '88' => $this->processContinuation($line),
            default => $this->processUnknown()
        };
    }

    // -- getters --------------------------------------------------------------

    // TODO(zmd): implement getters

    // -- helper methods -------------------------------------------------------

    protected function recordField(string $fieldKey): null|string|int
    {
        try {
            return $this->recordParser[$fieldKey];
        } catch (\Error) {
            throw new MalformedInputException('Cannot access a field prior to reading an incoming Transaction line.');
        } catch (InvalidTypeException $e) {
            throw new MalformedInputException("Encountered issue trying to parse Transaction Field. {$e->getMessage()}");
        } catch (ParseException) {
            throw new MalformedInputException('Cannot access a field from an incomplete or malformed Transaction line.');
        }
    }

    protected function processRecord(string $recordCode, string $line): void
    {
        $this->recordParser = new TransactionParser(
            physicalRecordLength: $this->physicalRecordLength,
        );
        $this->recordParser->pushLine($line);
    }

    protected function processContinuation(string $line): void
    {
        if (isset($this->recordParser)) {
            $this->recordParser->pushLine($line);
        } else {
            throw new MalformedInputException('Cannot process a continuation without first processing something that can be continued.');
        }
    }

    protected function processUnknown(): void
    {
        // TODO(zmd): implement me; make it go BANG!!
    }

}
