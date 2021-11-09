<?php

namespace STS\Bai2\Exceptions;

/**
 * Encountered an incoming record line while parsing which did not fit into the
 * current parsing context (e.g. we expected a header line but got some other
 * record type or a malformed input line).
 */
class InvalidRecordException extends ParseException
{

}
