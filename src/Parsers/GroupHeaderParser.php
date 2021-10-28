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
        $this->parsed['recordCode'] = $this->getParser()->shift();

        // TODO(zmd): validate format & default/optional
        $this->parsed['ultimateReceiverIdentification'] =
            $this->parseField($this->getParser()->shift(), 'Ultimate Receiver Identification')
                 ->string(default: null);

        // TODO(zmd): validate format & default/optional
        $this->parsed['originatorIdentification'] =
            $this->parseField($this->getParser()->shift(), 'Originator Identification')
                 ->string(default: null);

        // TODO(zmd): validate format & default/optional
        $this->parsed['groupStatus'] =
            $this->parseField($this->getParser()->shift(), 'Group Status')
                 ->string(default: null);

        // TODO(zmd): validate format & default/optional
        $this->parsed['asOfDate'] =
            $this->parseField($this->getParser()->shift(), 'As-of-Date')
                 ->string(default: null);

        // TODO(zmd): validate format & default/optional
        $this->parsed['asOfTime'] =
            $this->parseField($this->getParser()->shift(), 'As-of-Time')
                 ->string(default: null);

        // TODO(zmd): validate format & default/optional
        $this->parsed['currencyCode'] =
            $this->parseField($this->getParser()->shift(), 'Currency Code')
                 ->string(default: null);

        // TODO(zmd): validate format & default/optional
        $this->parsed['asOfDateModifier'] =
            $this->parseField($this->getParser()->shift(), 'As-of-Date Modifier')
                 ->string(default: null);

        return $this;
    }

}
