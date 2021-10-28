<?php

namespace STS\Bai2\Parsers;

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

        //
        //   --   ----------   -   :  ---   ------   -   -    :  ---   --------   -   -
        //   03 , 0975312468 ,   ,    010 , 500000 ,   ,   ,     190 , 70000000 , 4 , 0 /
        //                            ^                          ^
        //                            |                          |
        //                    acct status follows        acct summary follows
        //
        // Defaulted Type Code:
        //
        //     * null
        //
        //   "Default indicates that no status or summary data are being
        //    reported."
        //
        //        - Record Formats, pg. 15 (pdf 20/104)
        //
        //   "Note: An 03 record must include an account number but might not
        //    include status or summary data. For example, an 03 record would
        //    not report status or summary data if it is used only to identify
        //    the account number for Transaction Detail (16) records that
        //    follow. In this case, the account number would be followed by
        //    five commas and a slash ',,,,,/' to delimit the Currency Code,
        //    Type Code, Amount, Item Count and Funds Type fields, which are
        //    defaulted."
        //
        //        - Record Formats, pg. 17 (pdf 22/104)
        //
        //   'typeCode'       => null
        //
        // Account Status Type Codes:
        //
        //     * 001–099
        //     * 900-919
        //
        //   "Status type codes may not be accompanied by an item count or a
        //    funds type distribution."
        //
        //       - Appendix A, pg. 44 (pdf 49/104)
        //
        //   "[Item Count] for summary type codes only; must be defaulted for
        //    Status type codes."
        //
        //        - Record Formats, pg. 15 (pdf 20/104)
        //
        //   "Type 03 records allow the reporting of item counts and funds
        //    availability for summary data only. Status availability is
        //    reported by individual type codes (e.g., type code 072, one-day
        //    float). The 'Item Count' and 'Funds Type' fields following a
        //    status amount should be defaulted by adjacent delimiters."
        //
        //        - Record Formats, pg. 16 (pdf 21/104)
        //
        //   "[Item Count] used only with activity summary type codes. This
        //    field should be defaulted for account status type codes."
        //
        //        - Data Elements, pg. 34 (pdf 39/104)
        //
        //   'typeCode'       => '010'
        //   'accountStatus' => [ 'amount' => 500000 ]
        //
        // Account Summary Type Codes:
        //
        //     * 100-799
        //     * 920-999
        //
        //   "Summary amounts are always positive or unsigned.  Summary type
        //    codes may be accompanied by an item count or funds type
        //    distribution."
        //
        //       - Appendix A, pg. 44 (pdf 49/104)
        //
        //   "Type 03 records allow the reporting of item counts and funds
        //    availability for summary data only. Status availability is
        //    reported by individual type codes (e.g., type code 072, one-day
        //    float). The 'Item Count' and 'Funds Type' fields following a
        //    status amount should be defaulted by adjacent delimiters."
        //
        //        - Record Formats, pg. 16 (pdf 21/104)
        //
        //   "[Item Count] for summary type codes only; must be defaulted for
        //    Status type codes."
        //
        //        - Record Formats, pg. 15 (pdf 20/104)
        //
        //   'typeCode'       => '190'
        //   'accountSummary' => [ 'amount' => 70000000,
        //                         'itemCount' => 4,
        //                         'fundsType' => '0' ]
        //
        //    NOTE: The rules for Funds Type are even more complicated; it may
        //    be a single value (as this example), or an aggregate of it's own.
        //

        // TODO(zmd): finish implementing me!
        return $this;
    }

}
