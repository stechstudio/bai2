<?php

namespace STS\Bai2\Parsers;

trait FundsTypeFieldParserTrait
{

    protected function shiftAndParseFundsType(): array
    {
        $fundsType = [];

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

                // TODO(zmd): validate format & default/optional
                $fundsType['availability'][0] =
                    $this->shiftAndParseField('Funds Type, Distributed Availability (S), Immediate Availability')
                         ->int(default: null);

                // TODO(zmd): validate format & default/optional
                $fundsType['availability'][1] =
                    $this->shiftAndParseField('Funds Type, Distributed Availability (S), One-day Availability')
                         ->int(default: null);

                // TODO(zmd): validate format & default/optional
                $fundsType['availability'][2] =
                    $this->shiftAndParseField('Funds Type, Distributed Availability (S), Two-or-more Day Availability')
                         ->int(default: null);

                break;

            case 'D':
                // TODO(zmd): validate format & default/optional
                $numDistributions =
                    $this->shiftAndParseField('Funds Type, Distributed Availability (D), Number of Distributions')
                         ->int(default: null);

                for (; $numDistributions > 0; --$numDistributions) {
                    // TODO(zmd): validate format & default/optional
                    $days = $this->shiftAndParseField('Funds Type, Distributed Availability (D), Specified Availability')
                                 ->int(default: null);

                    // TODO(zmd): validate format & default/optional
                    $amount = $this->shiftAndParseField('Funds Type, Distributed Availability (D), Amount')
                                   ->int(default: null);

                    $fundsType['availability'][$days] = $amount;
                }

                break;
        }

        return $fundsType;
    }

}
