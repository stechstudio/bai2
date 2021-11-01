<?php

namespace STS\Bai2\Parsers;

/**
 * # Account Identifier and Summary Status
 *
 * * __Defaulted Type Code__:
 *     * `null`
 * * __Account Status Type Codes__:
 *     * `"001"` - `"099"`
 *     * `"900"` - `"919"`
 * * __Account Summary Type Codes__:
 *     * `"100"` - `"799"`
 *     * `"920"` - `"999"`
 *
 * ## Example Record Breakdown
 *
 * ```
 *                            -----------------------    ----------------------
 *   --   ----------   -   :  ---   ------   -   -    :  ---   --------   -   - : ...
 *   03 , 0975312468 ,   ,    010 , 500000 ,   ,   ,     190 , 70000000 , 4 , 0 /
 *                            ^                          ^
 *                            |                          |
 *                    acct status follows        acct summary follows
 * ```
 *
 * ## Selected Readings from the BAI2 Specification:
 *
 * > This record identifies the account number and reports summary and status
 * > information. Summary information may be accompanied by an item count and
 * > funds availability distribution.
 *
 * --_Record Formats, pg. 15 (pdf 20/104)_
 *
 * > Default indicates that no status or summary data are being reported.
 *
 * --_Record Formats, pg. 15 (pdf 20/104)_
 *
 * > [Item Count is] for summary type codes only; must be defaulted for Status
 * > type codes.
 *
 * --_Record Formats, pg. 15 (pdf 20/104)_
 *
 * > Type 03 records allow the reporting of item counts and funds availability
 * > for summary data only. Status availability is reported by individual type
 * > codes (e.g., type code 072, one-day float). The 'Item Count' and 'Funds
 * > Type' fields following a status amount should be defaulted by adjacent
 * > delimiters.
 *
 * --_Record Formats, pg. 16 (pdf 21/104)__
 *
 * > Note: An 03 record must include an account number but might not include
 * > status or summary data. For example, an 03 record would not report status
 * > or summary data if it is used only to identify the account number for
 * > Transaction Detail (16) records that follow. In this case, the account
 * > number would be followed by five commas and a slash ',,,,,/' to delimit
 * > the Currency Code, Type Code, Amount, Item Count and Funds Type fields,
 * > which are defaulted.
 *
 * --_Record Formats, pg. 17 (pdf 22/104)_
 *
 * > [Item Count] used only with activity summary type codes. This field should
 * > be defaulted for account status type codes.
 *
 * --_Data Elements, pg. 34 (pdf 39/104)_
 *
 * > Only one amount for each status or summary type code can remain on file
 * > for each account on an As-of-Date.
 *
 * --_Data Elements, pg. 38 (pdf 43/104)_
 *
 * > Summary amounts are always positive or unsigned. Summary type codes may be
 * > accompanied by an item count or funds type distribution.
 *
 * --_Appendix A, pg. 44 (pdf 49/104)_
 *
 * > Status type codes may not be accompanied by an item count or a funds type
 * > distribution.
 *
 * --_Appendix A, pg. 44 (pdf 49/104)_
 *
 * _(NOTE: The rules for Funds Type are even more complicated; it may be a
 * single value (as this example), or an aggregate of it's own.)_
 */
final class AccountHeaderParser extends AbstractRecordParser
{
    use RecordParserTrait;

    public function __construct(protected ?int $physicalRecordLength = null)
    {
    }

    protected static function recordCode(): string
    {
        return '03';
    }

    protected static function readableClassName(): string
    {
        return 'Account Identifier and Summary Status';
    }

    protected function parseFields(): self
    {
        $this->parsed['recordCode'] = $this->shiftField();

        // TODO(zmd): validate format & default/optional
        $this->parsed['customerAccountNumber'] =
            $this->shiftAndParseField('Customer Account Number')
                 ->string(default: null);

        // TODO(zmd): validate format & default/optional
        $this->parsed['currencyCode'] =
            $this->shiftAndParseField('Currency Code')
                 ->string(default: null);

        // TODO(zmd): clean this mess up!
        $this->parsed['summaryAndStatusInformation'] = [];
        while ($this->getParser()->hasMore()) {
            // TODO(zmd): validate format & default/optional
            $typeCode =
                $this->shiftAndParseField('Type Code')
                     ->string(default: null);

            if ($typeCode) {
                $accountInformationOrStatus = [ 'typeCode' => $typeCode ];

                $typeCodeInt = (int) $typeCode;
                if (
                    ($typeCodeInt >=   1 && $typeCodeInt <=  99) ||
                    ($typeCodeInt >= 900 && $typeCodeInt <= 919)
                ) {
                    // Account Status Type Code (itemCount and fundsType should
                    // be defaulted)

                    // TODO(zmd): validate format & default/optional (can be
                    //   signed or unsigned)
                    $accountInformationOrStatus['amount'] =
                        $this->shiftAndParseField('Amount')
                             ->int(default: null);

                    // TODO(zmd): validate format & default/optional (they
                    //   should be ->is(''))
                    $this->getParser()->drop(2);
                } else if (
                    ($typeCodeInt >= 100 && $typeCodeInt <= 799) ||
                    ($typeCodeInt >= 920 && $typeCodeInt <= 999)
                ) {
                    // Account Summary Type Code (may have itemCount and
                    // fundsType fields)

                    // TODO(zmd): validate format & default/optional (unsigned
                    //   only; can NOT be signed)
                    $accountInformationOrStatus['amount'] =
                        $this->shiftAndParseField('Amount')
                             ->int(default: null);

                    // TODO(zmd): validate format & default/optional
                    $accountInformationOrStatus['itemCount'] =
                        $this->shiftAndParseField('Item Count')
                             ->int(default: null);

                    $accountInformationOrStatus['fundsType']
                        = $this->shiftAndParseFundsType();
                } else {
                    // TODO(zmd): validate if encountering an *obviously* invalid
                    //   type code range?
                }

                $this->parsed['summaryAndStatusInformation'][] = $accountInformationOrStatus;
            } else {
                // Defaulted Type Code
                // TODO(zmd): validate format & default/optional (they should be ->is(''))
                $this->getParser()->drop(3);
            }
        }

        return $this;
    }

    // TODO(zmd) extract parses funds type trait (used here and in transaction
    //  parser)
    protected function shiftAndParseFundsType(): array
    {
        $fundsType = [];

        // TODO(zmd): validate format & default/optional
        $fundsType['distributionOfAvailability'] =
            $this->shiftAndParseField('Funds Type, Distribution of Availability')
                 ->string(default: null);

        // TODO(zmd): finish implementing me!
        switch ($fundsType['distributionOfAvailability']) {
            case 'Z':
            case '0':
            case '1':
            case '2':
                break;

            case 'V':
                // TODO(zmd): validate format & default/optional
                $fundsType['valueDate'] =
                    $this->shiftAndParseField('Funds Type, Value Dated, Date')
                         ->string(default: null);

                // TODO(zmd): validate format & default/optional
                $fundsType['valueTime'] =
                    $this->shiftAndParseField('Funds Type, Value Dated, Time')
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
                // TODO(zmd): parse D-type distributed availability funds type info
                //   D,5,0,150000,1,100000,3,90000,5,70000,,7,50000
                //     |
                //     V
                //   0 => 150000,
                //   1 => 100000,
                //   3 =>  90000,
                //   5 =>  70000,
                //   7 =>  50000,
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
