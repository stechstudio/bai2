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

        // TODO(zmd): validate format & default/optional
        $this->parsed['asOfTime'] =
            $this->shiftAndParseField('As-of-Time')
                 ->string(default: null);

        // TODO(zmd): validate format & default/optional
        $this->parsed['currencyCode'] =
            $this->shiftAndParseField('Currency Code')
                 ->string(default: null);

        // TODO(zmd): validate format & default/optional
        $this->parsed['asOfDateModifier'] =
            $this->shiftAndParseField('As-of-Date Modifier')
                 ->string(default: null);

        return $this;
    }

}
