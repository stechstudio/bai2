<?php

namespace STS\Bai2\Parsers;

use STS\Bai2\Tests\Parsers\RecordParserTestCase;

/**
 * @group RecordParserTests
 */
final class FileTrailerParserTest extends RecordParserTestCase
{

    protected static string $parserClass = FileTrailerParser::class;

    protected static string $readableParserName = 'File Trailer';

    protected static string $recordCode = '99';

    protected static string $fullRecordLine = '99,123456789,5,42/';

    protected static string $partialRecordLine = '99,123456789/';

    private static string $continuedRecordLine = '88,5,42/';

}
