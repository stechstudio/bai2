<?php

declare(strict_types=1);

namespace STS\Bai2\Records;

use STS\Bai2\Bai2;

use STS\Bai2\Parsers\TransactionParser;

use STS\Bai2\Exceptions\MalformedInputException;
use STS\Bai2\Exceptions\InvalidTypeException;
use STS\Bai2\Exceptions\ParseException;

class TransactionRecord
{

    protected TransactionParser $parser;

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
        try {
            return $this->parser[$fieldKey];
        } catch (\Error) {
            throw new MalformedInputException('Cannot access a Transaction field prior to reading an incoming Transaction line.');
        } catch (InvalidTypeException $e) {
            throw new MalformedInputException("Encountered issue trying to parse Transaction Field. {$e->getMessage()}");
        } catch (ParseException) {
            throw new MalformedInputException('Cannot access a Transaction field from an incomplete or malformed Transaction line.');
        }
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
