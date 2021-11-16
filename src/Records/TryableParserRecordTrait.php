<?php

declare(strict_types=1);

namespace STS\Bai2\Records;

use STS\Bai2\Exceptions\MalformedInputException;
use STS\Bai2\Exceptions\InvalidTypeException;
use STS\Bai2\Exceptions\ParseException;

trait TryableParserRecordTrait
{

    protected function tryParser(
        string $propertyName,
        string $readableName,
        callable $cb
    ): mixed {
        try {
            return $cb($this->$propertyName);
        } catch (\Error) {
            throw new MalformedInputException("Cannot access a {$readableName} field prior to reading an incoming {$readableName} line.");
        } catch (InvalidTypeException $e) {
            throw new MalformedInputException("Encountered issue trying to parse {$readableName} Field. {$e->getMessage()}");
        } catch (ParseException) {
            throw new MalformedInputException("Cannot access a {$readableName} field from an incomplete or malformed {$readableName} line.");
        }
    }

}
