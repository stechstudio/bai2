<?php

namespace STS\Bai2\Parsers;

/**
 * # Account Identifer and Summary Status
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


        // TODO(zmd): finish implementing me!
        return $this;
    }

}
