<?php

declare(strict_types=1);

namespace STS\Bai2\Parsers;

trait RecordParserIdempotentParsingTrait
{

    abstract protected function parseFields(): self;

    protected array $parsed = [];

    private function parseFieldsOnce(): self
    {
        if (empty($this->parsed)) {
            $this->parseFields();
        }

        return $this;
    }

}
