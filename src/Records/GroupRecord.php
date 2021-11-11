<?php

namespace STS\Bai2\Records;

use STS\Bai2\Bai2;

use STS\Bai2\Parsers\GroupHeaderParser;
use STS\Bai2\Parsers\GroupTrailerParser;

use STS\Bai2\Exceptions\MalformedInputException;
use STS\Bai2\Exceptions\InvalidTypeException;
use STS\Bai2\Exceptions\ParseException;

class GroupRecord
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

    public function getGroupControlTotal(): ?string
    {
        return $this->trailerField('groupControlTotal');
    }

    public function getNumberOfAccounts(): ?string
    {
        return $this->trailerField('numberOfAccounts');
    }

    public function getNumberOfRecords(): ?string
    {
        return $this->trailerField('numberOfRecords');
    }

    // -- helper methods -------------------------------------------------------

    protected function headerField(string $fieldKey): null|string|int
    {
        try {
            return $this->headerParser[$fieldKey];
        } catch (\Error) {
            throw new MalformedInputException('Cannot access a Group Header field prior to reading an incoming Group Header line.');
        } catch (InvalidTypeException $e) {
            throw new MalformedInputException("Encountered issue trying to parse Group Header Field. {$e->getMessage()}");
        } catch (ParseException) {
            throw new MalformedInputException('Cannot access a Group Header field from an incomplete or malformed Group Header line.');
        }
    }

    protected function trailerField(string $fieldKey): null|string|int
    {
        try {
            return $this->trailerParser[$fieldKey];
        } catch (\Error) {
            throw new MalformedInputException('Cannot access a Group Trailer field prior to reading an incoming Group Trailer line.');
        } catch (InvalidTypeException $e) {
            throw new MalformedInputException("Encountered issue trying to parse Group Trailer Field. {$e->getMessage()}");
        } catch (ParseException) {
            throw new MalformedInputException('Cannot access a Group Trailer field from an incomplete or malformed Group Trailer line.');
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
