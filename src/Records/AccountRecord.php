<?php

declare(strict_types=1);

namespace STS\Bai2\Records;

use STS\Bai2\Parsers\AccountHeaderParser;
use STS\Bai2\Parsers\AccountTrailerParser;

use STS\Bai2\Exceptions\MalformedInputException;

class AccountRecord extends AbstractRecord
{
    use RecordCodePeekTrait;
    use TryableParserRecordTrait;

    protected AccountHeaderParser $headerParser;

    protected AccountTrailerParser $trailerParser;

    protected array $transactions = [];

    protected TransactionRecord $currentChild;

    public function __construct(protected ?int $physicalRecordLength)
    {
    }

    public function parseLine(string $line): void
    {
        match ($recordCode = static::recordTypeCode($line)) {
            '03' => $this->processHeader($recordCode, $line),
            '88' => $this->processContinuation($line),
            '49' => $this->processTrailer($recordCode, $line),
            default => $this->processChildRecord($recordCode, $line)
        };
    }

    public function toArray(): array
    {
        $headerArray = $this->tryHeaderParser(fn($p) => $p->toArray());
        $trailerArray = $this->tryTrailerParser(fn($p) => $p->toArray());

        $txnsArray = [
            'transactions' => array_map(
                fn($txn) => $txn->toArray(),
                $this->transactions
            )
        ];

        $combinedArray = $headerArray + $trailerArray + $txnsArray;
        unset($combinedArray['recordCode']);

        return $combinedArray;
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

    public function getTransactions(): array
    {
        return $this->transactions;
    }

    // -- helper methods -------------------------------------------------------

    protected function headerField(string $fieldKey): null|string|int|array
    {
        return $this->tryHeaderParser(fn($p) => $p[$fieldKey]);
    }

    protected function trailerField(string $fieldKey): null|string|int
    {
        return $this->tryTrailerParser(fn($p) => $p[$fieldKey]);
    }

    protected function tryHeaderParser(callable $cb): mixed
    {
        return $this->tryParser(
            'headerParser',
            'Account Identifier and Summary Status',
            $cb
        );
    }

    protected function tryTrailerParser(callable $cb): mixed
    {
        return $this->tryParser(
            'trailerParser',
            'Account Trailer',
            $cb
        );
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
