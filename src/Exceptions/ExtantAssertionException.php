<?php

namespace STS\Bai2\Exceptions;

/**
 * Thrown any time a Bai2 component should definitely exist but didn't
 * (indicating likely error in usage).
 */
class ExtantAssertionException extends InvalidUseException
{

}
