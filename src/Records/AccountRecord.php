<?php

declare(strict_types=1);

namespace STS\Bai2\Records;

use STS\Bai2\Bai2;

use STS\Bai2\Parsers\AccountHeaderParser;
use STS\Bai2\Parsers\AccountTrailerParser;

use STS\Bai2\Exceptions\MalformedInputException;
use STS\Bai2\Exceptions\InvalidTypeException;
use STS\Bai2\Exceptions\ParseException;

class AccountRecord
{

    protected AccountHeaderParser $headerParser;

    protected AccountTrailerParser $trailerParser;

    protected array $transactions = [];

    protected TransactionRecord $currentChild;

    public function __construct(protected ?int $physicalRecordLength)
    {
    }

    public function parseLine(string $line): void
    {
        match ($recordCode = Bai2::recordTypeCode($line)) {
            '03' => $this->processHeader($recordCode, $line),
            '88' => $this->processContinuation($line),
            '49' => $this->processTrailer($recordCode, $line),
            default => $this->processChildRecord($recordCode, $line)
        };
    }

    // -- getters --------------------------------------------------------------

    public function getCustomerAccountNumber(): string
    {
        return $this->headerField('customerAccountNumber');
    }

    public function getCurrencyCode(): ?string
    {
        return $this->headerField('currencyCode');
    }

    public function getSummaryAndStatusInformation(): array
    {
        return $this->headerField('summaryAndStatusInformation');
    }

    public function getAccountControlTotal(): int
    {
        return $this->trailerField('accountControlTotal');
    }

    public function getNumberOfRecords(): int
    {
        return $this->trailerField('numberOfRecords');
    }

    // -- helper methods -------------------------------------------------------

    protected function headerField(string $fieldKey): null|string|int|array
    {
        try {
            return $this->headerParser[$fieldKey];
        } catch (\Error) {
            throw new MalformedInputException('Cannot access a Account Identifier and Summary Status field prior to reading an incoming Account Identifier and Summary Status line.');
        } catch (InvalidTypeException $e) {
            throw new MalformedInputException("Encountered issue trying to parse Account Identifier and Summary Status Field. {$e->getMessage()}");
        } catch (ParseException) {
            throw new MalformedInputException('Cannot access a Account Identifier and Summary Status field from an incomplete or malformed Account Identifier and Summary Status line.');
        }
    }

    protected function trailerField(string $fieldKey): null|string|int
    {
        try {
            return $this->trailerParser[$fieldKey];
        } catch (\Error) {
            throw new MalformedInputException('Cannot access a Account Trailer field prior to reading an incoming Account Trailer line.');
        } catch (InvalidTypeException $e) {
            throw new MalformedInputException("Encountered issue trying to parse Account Trailer Field. {$e->getMessage()}");
        } catch (ParseException) {
            throw new MalformedInputException('Cannot access a Account Trailer field from an incomplete or malformed Account Trailer line.');
        }
    }

    protected function processHeader(string $recordCode, string $line): void
    {
        $this->headerParser = new AccountHeaderParser(
            physicalRecordLength: $this->physicalRecordLength,
        );
        $this->headerParser->pushLine($line);
    }

    protected function processTrailer(string $recordCode, string $line): void
    {
        $this->trailerParser = new AccountTrailerParser(
            physicalRecordLength: $this->physicalRecordLength,
        );
        $this->trailerParser->pushLine($line);
    }

    protected function processContinuation(string $line): void
    {
        if (isset($this->trailerParser)) {
            $this->trailerParser->pushLine($line);
        } else if (isset($this->currentChild)) {
            $this->currentChild->parseLine($line);
        } else if (isset($this->headerParser)) {
            $this->headerParser->pushLine($line);
        } else {
            throw new MalformedInputException('Cannot process a continuation without first processing something that can be continued.');
        }
    }

    protected function processChildRecord(string $recordCode, string $line): void
    {
        if ($recordCode == '16') {
            $this->currentChild = new TransactionRecord(
                physicalRecordLength: $this->physicalRecordLength
            );
            $this->transactions[] = $this->currentChild;
        }

        try {
            $this->currentChild->parseLine($line);
        } catch (\Error $e) {
            throw new MalformedInputException('Cannot process Transaction-related line before processing the main Transaction line.');
        }
    }

}
