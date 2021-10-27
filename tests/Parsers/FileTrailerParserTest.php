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

    // ----- record-specific parsing and usage ---------------------------------

    // TODO(zmd): public function testParseFromSingleLine(): void {}

    // TODO(zmd): public function testParseFromMultipleLines(): void {}

    // TODO(zmd): public function testPhysicalRecordLengthEnforcedOnFirstLine(): void {}

    // TODO(zmd): public function testPhysicalRecordLengthEnforcedOnSubsequentLine(): void {}

    // TODO(zmd): public function testToArray(): void {}

    // ----- record-specific field validation ----------------------------------

    // TODO(zmd): test "File Control Total" validation

    // TODO(zmd): test "Number of Groups" validation

    // TODO(zmd): test "Number of Records" validation

}
