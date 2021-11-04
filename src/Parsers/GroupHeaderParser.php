<?php

namespace STS\Bai2\Parsers;

final class GroupHeaderParser extends AbstractRecordParser
{
    use RecordParserTrait;

    public function __construct(protected ?int $physicalRecordLength = null)
    {
    }

    protected static function recordCode(): string
    {
        return '02';
    }

    protected function parseFields(): self
    {
        $this->parsed['recordCode'] = $this->shiftField();

        $this->parsed['ultimateReceiverIdentification'] =
            $this->shiftAndParseField('Ultimate Receiver Identification')
                 ->match('/^[[:alnum:]]+$/', 'must be alpha-numeric when provided')
                 ->string(default: null);

        $this->parsed['originatorIdentification'] =
            $this->shiftAndParseField('Originator Identification')
                 ->match('/^[[:alnum:]]+$/', 'must be alpha-numeric')
                 ->string();

        $this->parsed['groupStatus'] =
            $this->shiftAndParseField('Group Status')
                 ->match('/^[1-4]$/', 'must be one of 1, 2, 3, or 4')
                 ->string();

        $this->parsed['asOfDate'] =
            $this->shiftAndParseField('As-of-Date')
                 ->match('/^\d{6}$/', 'must be exactly 6 numerals (YYMMDD)')
                 ->string();

        $this->parsed['asOfTime'] =
            $this->shiftAndParseField('As-of-Time')
                 ->match('/^\d{4}$/', 'must be exactly 4 numerals (HHMM) when provided')
                 ->string(default: null);

        $this->parsed['currencyCode'] =
            $this->shiftAndParseField('Currency Code')
                 ->match('/^[A-Z]{3}$/', 'must be exactly 3 uppercase letters when provided')
                 ->string(default: null);

        $this->parsed['asOfDateModifier'] =
            $this->shiftAndParseField('As-of-Date Modifier')
                 ->match('/^[1-4]$/', 'must be one of 1, 2, 3, or 4 when provided')
                 ->string(default: null);

        return $this;
    }

}
