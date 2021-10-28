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

        // TODO(zmd): validate format
        $this->parsed['ultimateReceiverIdentification'] =
            $this->parseField($this->getParser()->shift(), 'Ultimate Receiver Identification')
                 ->string();

        // TODO(zmd): validate format
        $this->parsed['originatorIdentification'] =
            $this->parseField($this->getParser()->shift(), 'Originator Identification')
                 ->string();

        // TODO(zmd): validate format
        $this->parsed['groupStatus'] =
            $this->parseField($this->getParser()->shift(), 'Group Status')
                 ->string();

        // TODO(zmd): validate format
        $this->parsed['asOfDate'] =
            $this->parseField($this->getParser()->shift(), 'As-of-Date')
                 ->string();

        // TODO(zmd): validate format
        $this->parsed['asOfTime'] =
            $this->parseField($this->getParser()->shift(), 'As-of-Time')
                 ->string();

        // TODO(zmd): validate format
        $this->parsed['currencyCode'] =
            $this->parseField($this->getParser()->shift(), 'Currency Code')
                 ->string();

        // TODO(zmd): validate format
        $this->parsed['asOfDateModifier'] =
            $this->parseField($this->getParser()->shift(), 'As-of-Date Modifier')
                 ->string();

        // TODO(zmd): finish implementing me!
        return $this;
    }

}
