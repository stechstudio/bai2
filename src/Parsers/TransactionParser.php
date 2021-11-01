<?php

namespace STS\Bai2\Parsers;

final class TransactionParser extends AbstractRecordParser
{
    use RecordParserTrait;
    use FundsTypeFieldParserTrait;

    public function __construct(protected ?int $physicalRecordLength = null)
    {
    }

    protected static function recordCode(): string
    {
        return '16';
    }

    protected function parseFields(): self
    {
        $this->parsed['recordCode'] = $this->shiftField();

        // TODO(zmd): validate format & default/optional
        $this->parsed['typeCode'] =
            $this->shiftAndParseField('Type Code')
                 ->string(default: null);

        // TODO(zmd): validate format & default/optional
        $this->parsed['amount'] =
            $this->shiftAndParseField('Amount')
                 ->int(default: null);

        $this->parsed['fundsType'] = $this->shiftAndParseFundsType();

        // TODO(zmd): validate format & default/optional
        $this->parsed['bankReferenceNumber'] =
            $this->shiftAndParseField('Bank Reference Number')
                 ->string(default: null);

        // TODO(zmd): validate format & default/optional
        $this->parsed['customerReferenceNumber'] =
            $this->shiftAndParseField('Customer Reference Number')
                 ->string(default: null);

        // TODO(zmd): validate format & default/optional
        $this->parsed['text'] =
            $this->getParser()->shiftText();

        return $this;
    }

}
