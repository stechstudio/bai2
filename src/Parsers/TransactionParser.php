<?php

namespace STS\Bai2\Parsers;

use STS\Bai2\Parsers\TransactionDetailTypeCode as TypeCode;

use STS\Bai2\Exceptions\InvalidTypeException;

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

        $this->parsed['typeCode'] =
            $this->shiftAndParseField('Type Code')
                 ->match('/^\d{3}$/', 'must be composed of exactly three numerals')
                 ->string();

        switch (TypeCode::detect($this->parsed['typeCode'])) {
            case TypeCode::NON_MONETARY:
                $this->parsed['amount'] =
                    $this->shiftAndParseField('Amount')
                         ->is('', 'should be defaulted since "Type Code" was non-monetary')
                         ->int(default: null);

                // TODO(zmd): if typeCode was 890, then fundsType should be
                //   defaulted
                break;

            case TypeCode::CREDIT:
            case TypeCode::DEBIT:
            case TypeCode::LOAN:
            case TypeCode::CUSTOM:
                // TODO(zmd): validate format & default/optional
                $this->parsed['amount'] =
                    $this->shiftAndParseField('Amount')
                         ->int(default: null);
                break;

            default:
                throw new InvalidTypeException('Invalid field type: "Type Code" was out outside the valid range for transaction detail data.');
                break;
        }

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
