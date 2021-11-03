<?php

namespace STS\Bai2\Parsers;

trait FundsTypeFieldParserTrait
{

    protected function shiftAndParseFundsType(): array
    {
        $fundsType = [];
        $signedIntConstraint = ['/^(-|\+)?\d+$/', 'must a signed or unsigned integer value'];

        $fundsType['distributionOfAvailability'] =
            $this->shiftAndParseField('Distribution of Availability')
                 ->match('/^(0|1|2|V|S|D|Z)$/', 'for "Funds Type" must be one of "0", "1", "2", "V", "S", "D", or "Z"')
                 ->string(default: null);

        switch ($fundsType['distributionOfAvailability']) {
            case 'Z':
            case '0':
            case '1':
            case '2':
                break;

            case 'V':
                $fundsType['valueDate'] =
                    $this->shiftAndParseField('Value Dated Date')
                         ->match('/^\d{6}$/', 'must be exactly 6 numerals (YYMMDD)')
                         ->string();

                $fundsType['valueTime'] =
                    $this->shiftAndParseField('Value Dated Time')
                         ->match('/^\d{4}$/', 'must be exactly 4 numerals (HHMM)')
                         ->string(default: null);

                break;

            case 'S':
                $fundsType['availability'] = [];

                $fundsType['availability'][0] =
                    $this->shiftAndParseField('Immediate Availability')
                         ->match(...$signedIntConstraint)
                         ->int();

                $fundsType['availability'][1] =
                    $this->shiftAndParseField('One-day Availability')
                         ->match(...$signedIntConstraint)
                         ->int();

                $fundsType['availability'][2] =
                    $this->shiftAndParseField('Two-or-more Day Availability')
                         ->match(...$signedIntConstraint)
                         ->int();

                break;

            case 'D':
                $fundsType['availability'] = [];

                $numDistributions =
                    $this->shiftAndParseField('Number of Distributions')
                         ->match('/^\d+$/', 'should be an unsigned integer')
                         ->int();

                for (; $numDistributions > 0; --$numDistributions) {
                    // TODO(zmd): validate format & default/optional
                    $days = $this->shiftAndParseField('Number of Distributions')
                                 ->int(default: null);

                    // TODO(zmd): validate format & default/optional
                    $amount = $this->shiftAndParseField('Number of Distributions')
                                   ->int(default: null);

                    $fundsType['availability'][$days] = $amount;
                }

                break;
        }

        return $fundsType;
    }

}
