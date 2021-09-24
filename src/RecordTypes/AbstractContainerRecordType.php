<?php
namespace STS\Bai2\RecordTypes;

use STS\Bai2\RecordTypes\AbstractRecordType;

abstract class AbstractContainerRecordType extends AbstractRecordType
{

    public ?AbstractRecordType $currentChild = null;

    protected function extantCurrentChild(): AbstractRecordType
    {
        if ($this->currentChild) {
            return $this->currentChild;
        }

        // TODO(zmd): more appropriate message, please.
        throw new \Exception('lolwut?');
    }

}
