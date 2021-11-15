<?php

declare(strict_types=1);

namespace STS\Bai2\Records;

use STS\Bai2\Bai2;

use STS\Bai2\Parsers\GroupHeaderParser;
use STS\Bai2\Parsers\GroupTrailerParser;

use STS\Bai2\Exceptions\MalformedInputException;
use STS\Bai2\Exceptions\InvalidTypeException;
use STS\Bai2\Exceptions\ParseException;

class GroupRecord extends AbstractRecord
{

    protected GroupHeaderParser $headerParser;

    protected GroupTrailerParser $trailerParser;

    protected array $accounts = [];

    protected AccountRecord $currentChild;

    public function __construct(protected ?int $physicalRecordLength)
    {
    }

    public function parseLine(string $line): void
    {
        match ($recordCode = Bai2::recordTypeCode($line)) {
            '02' => $this->processHeader($recordCode, $line),
            '88' => $this->processContinuation($line),
            '98' => $this->processTrailer($recordCode, $line),
            default => $this->processChildRecord($recordCode, $line)
        };
    }

    public function toArray(): array
    {
        $headerArray = $this->tryHeaderParser(fn($p) => $p->toArray());
        $trailerArray = $this->tryTrailerParser(fn($p) => $p->toArray());

        $accountsArray = [
            'accounts' => array_map(
                fn($account) => $account->toArray(),
                $this->accounts
            )
        ];

        $combinedArray = $headerArray + $trailerArray + $accountsArray;
        unset($combinedArray['recordCode']);

        return $combinedArray;
    }

    // -- getters --------------------------------------------------------------

    public function getUltimateReceiverIdentification(): ?string
    {
        return $this->headerField('ultimateReceiverIdentification');
    }

    public function getOriginatorIdentification(): string
    {
        return $this->headerField('originatorIdentification');
    }

    public function getGroupStatus(): string
    {
        return $this->headerField('groupStatus');
    }

    public function getAsOfDate(): string
    {
        return $this->headerField('asOfDate');
    }

    public function getAsOfTime(): ?string
    {
        return $this->headerField('asOfTime');
    }

    public function getCurrencyCode(): ?string
    {
        return $this->headerField('currencyCode');
    }

    public function getAsOfDateModifier(): ?string
    {
        return $this->headerField('asOfDateModifier');
    }

    public function getGroupControlTotal(): int
    {
        return $this->trailerField('groupControlTotal');
    }

    public function getNumberOfAccounts(): int
    {
        return $this->trailerField('numberOfAccounts');
    }

    public function getNumberOfRecords(): int
    {
        return $this->trailerField('numberOfRecords');
    }

    public function getAccounts(): array
    {
        return $this->accounts;
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
            'Group Header',
            $cb
        );
    }

    protected function tryTrailerParser(callable $cb): mixed
    {
        return $this->tryParser(
            'trailerParser',
            'Group Trailer',
            $cb
        );
    }

    protected function tryParser(
        string $propertyName,
        string $readableName,
        callable $cb
    ): mixed {
        try {
            return $cb($this->$propertyName);
        } catch (\Error) {
            throw new MalformedInputException("Cannot access a {$readableName} field prior to reading an incoming {$readableName} line.");
        } catch (InvalidTypeException $e) {
            throw new MalformedInputException("Encountered issue trying to parse {$readableName} Field. {$e->getMessage()}");
        } catch (ParseException) {
            throw new MalformedInputException("Cannot access a {$readableName} field from an incomplete or malformed {$readableName} line.");
        }
    }

    protected function processHeader(string $recordCode, string $line): void
    {
        $this->headerParser = new GroupHeaderParser(
            physicalRecordLength: $this->physicalRecordLength,
        );
        $this->headerParser->pushLine($line);
    }

    protected function processTrailer(string $recordCode, string $line): void
    {
        $this->trailerParser = new GroupTrailerParser(
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
        if ($recordCode == '03') {
            $this->currentChild = new AccountRecord(
                physicalRecordLength: $this->physicalRecordLength
            );
            $this->accounts[] = $this->currentChild;
        }

        try {
            $this->currentChild->parseLine($line);
        } catch (\Error) {
            throw new MalformedInputException('Cannot process Account Trailer or Transaction-related line before processing the Account Header line.');
        }
    }

}
