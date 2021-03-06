<?php

declare(strict_types=1);

namespace STS\Bai2\Records;

use STS\Bai2\Parsers\TransactionParser;

use STS\Bai2\Exceptions\MalformedInputException;

class TransactionRecord extends AbstractRecord
{
    use RecordCodePeekTrait;
    use TryableParserRecordTrait { tryParser as generalizedTryParser; }

    protected TransactionParser $parser;

    public function __construct(protected ?int $physicalRecordLength)
    {
    }

    public function parseLine(string $line): void
    {
        match ($recordCode = static::recordTypeCode($line)) {
            '16' => $this->processRecord($recordCode, $line),
            '88' => $this->processContinuation($line),
            default => $this->processUnknown()
        };
    }

    public function toArray(): array
    {
        $a = $this->tryParser(fn($parser) => $parser->toArray());
        unset($a['recordCode']);

        return $a;
    }

    // -- getters --------------------------------------------------------------

    public function getTypeCode(): string
    {
        return $this->field('typeCode');
    }

    public function getAmount(): ?int
    {
        return $this->field('amount');
    }

    public function getFundsType(): array
    {
        return $this->field('fundsType');
    }

    public function getBankReferenceNumber(): ?string
    {
        return $this->field('bankReferenceNumber');
    }

    public function getCustomerReferenceNumber(): ?string
    {
        return $this->field('customerReferenceNumber');
    }

    public function getText(): ?string
    {
        return $this->field('text');
    }

    // -- helper methods -------------------------------------------------------

    protected function field(string $fieldKey): null|string|int|array
    {
        return $this->tryParser(fn($parser) => $parser[$fieldKey]);
    }

    protected function tryParser(callable $cb): mixed
    {
        return $this->generalizedTryParser('parser', 'Transaction', $cb);
    }

    protected function processRecord(string $recordCode, string $line): void
    {
        $this->parser = new TransactionParser(
            physicalRecordLength: $this->physicalRecordLength,
        );
        $this->parser->pushLine($line);
    }

    protected function processContinuation(string $line): void
    {
        if (isset($this->parser)) {
            $this->parser->pushLine($line);
        } else {
            throw new MalformedInputException('Cannot process a continuation without first processing something that can be continued.');
        }
    }

    protected function processUnknown(): void
    {
        throw new MalformedInputException("Encountered an unknown record type code. Whatever we're seeing, it's not part of the BAI2 specification!");
    }

}
