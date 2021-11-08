<?php

namespace STS\Bai2\Parsers;

use STS\Bai2\Parsers\TransactionDetailTypeCode as TypeCode;

use STS\Bai2\Exceptions\InvalidTypeException;
use STS\Bai2\Exceptions\ParseException;

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
                $this->parseNonMonetaryAmountAndFundsType();
                break;

            case TypeCode::CREDIT:
            case TypeCode::DEBIT:
            case TypeCode::LOAN:
            case TypeCode::CUSTOM:
                $this->parseMonetaryAmountAndFundsType();
                break;

            default:
                throw new InvalidTypeException('Invalid field type: "Type Code" was out outside the valid range for transaction detail data.');
                break;
        }

        $this->parsed['bankReferenceNumber'] =
            $this->shiftAndParseField('Bank Reference Number')
                 ->match('/^[[:alnum:]]+$/', 'must be alpha-numeric when provided')
                 ->string(default: null);

        $this->parsed['customerReferenceNumber'] =
            $this->shiftAndParseField('Customer Reference Number')
                 ->match('/^[[:alnum:]]+$/', 'must be alpha-numeric when provided')
                 ->string(default: null);

        try {
            $this->parsed['text'] = $this->getParser()->shiftText();
        } catch (ParseException) {
            throw new InvalidTypeException('Invalid field type: "Text" mustn\'t begin with slash, and MUST end with slash if defaulted.');
        }

        return $this;
    }

    protected function parseNonMonetaryAmountAndFundsType(): void
    {
        $this->parsed['amount'] =
            $this->shiftAndParseField('Amount')
                 ->is('', 'should be defaulted since "Type Code" was non-monetary')
                 ->int(default: null);

            $this->parsed['fundsType'] = [];
            $this->parsed['fundsType']['distributionOfAvailability'] =
                $this->shiftAndParseField('Funds Type')
                     ->is('', 'should be defaulted since "Type Code" was non-monetary')
                     ->int(default: null);
    }

    protected function parseMonetaryAmountAndFundsType(): void
    {
        $this->parsed['amount'] =
            $this->shiftAndParseField('Amount')
                 ->match('/^\d+$/', 'should be an unsigned integer when provided')
                 ->int(default: null);

        $this->parsed['fundsType'] = $this->shiftAndParseFundsType();
    }

}
