<?php

declare(strict_types=1);

namespace STS\Bai2\Parsers;

use STS\Bai2\Tests\Parsers\RecordParserTestCase;

use STS\Bai2\Exceptions\InvalidTypeException;

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

    protected static string $continuedRecordLine = '88,5,42/';

    // ----- record-specific parsing and usage ---------------------------------

    public function testParseFromSingleLine(): void
    {
        $this->parser->pushLine(self::$fullRecordLine);

        $this->assertEquals('99', $this->parser['recordCode']);
        $this->assertEquals(123456789, $this->parser['fileControlTotal']);
        $this->assertEquals(5, $this->parser['numberOfGroups']);
        $this->assertEquals(42, $this->parser['numberOfRecords']);
    }

    public function testParseFromMultipleLines(): void
    {
        $this->parser->pushLine(self::$partialRecordLine);
        $this->parser->pushLine(self::$continuedRecordLine);

        $this->assertEquals('99', $this->parser['recordCode']);
        $this->assertEquals(123456789, $this->parser['fileControlTotal']);
        $this->assertEquals(5, $this->parser['numberOfGroups']);
        $this->assertEquals(42, $this->parser['numberOfRecords']);
    }

    public function testToArray(): void
    {
        $this->parser->pushLine(self::$fullRecordLine);

        $this->assertEquals(
            [
                'recordCode' => '99',
                'fileControlTotal' => 123456789,
                'numberOfGroups' => 5,
                'numberOfRecords' => 42,
            ],
            $this->parser->toArray()
        );
    }

    // ----- record-specific field validation ----------------------------------

    /**
     * @testWith ["99,123456789,5,42/", 123456789]
     *           ["99,+123456789,5,42/", 123456789]
     *           ["99,-123456789,5,42/", -123456789]
     *           ["99,000000001,5,42/", 1]
     *           ["99,0,5,42/", 0]
     *           ["99,1,5,42/", 1]
     */
    public function testFileControlTotalValid(
        string $line,
        int $expected
    ): void {
        $this->parser->pushLine($line);
        $this->assertEquals($expected, $this->parser['fileControlTotal']);
    }

    public function testFileControlTotalMissing(): void
    {
        $this->parser->pushLine('99,,5,42/');

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "File Control Total" cannot be omitted.');
        $this->parser['fileControlTotal'];
    }

    /**
     * @testWith ["99, 123456789,5,42/"]
     *           ["99,123456789 ,5,42/"]
     *           ["99,a123456789,5,42/"]
     *           ["99,123456789b,5,42/"]
     *           ["99,123_456_789,5,42/"]
     *           ["99,123+456+789,5,42/"]
     */
    public function testFileControlTotalInvalid(string $line): void
    {
        $this->parser->pushLine($line);

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "File Control Total" must be signed or unsigned integer.');
        $this->parser['fileControlTotal'];
    }

    /**
     * @testWith ["99,123456789,5,42/", 5]
     *           ["99,123456789,500,42/", 500]
     *           ["99,123456789,0,42/", 0]
     *           ["99,123456789,005,42/", 5]
     */
    public function testNumberOfGroupsValid(string $line, int $expected): void
    {
        $this->parser->pushLine($line);
        $this->assertEquals($expected, $this->parser['numberOfGroups']);
    }

    public function testNumberOfGroupsMissing(): void
    {
        $this->parser->pushLine('99,123456789,,42/');

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Number of Groups" cannot be omitted.');
        $this->parser['numberOfGroups'];
    }

    /**
     * @testWith ["99,123456789,-5,42/"]
     *           ["99,123456789,+5,42/"]
     *           ["99,123456789, 5,42/"]
     *           ["99,123456789,5 ,42/"]
     *           ["99,123456789,a5,42/"]
     *           ["99,123456789,5b,42/"]
     *           ["99,123456789,4_000,42/"]
     *           ["99,123456789,4+000,42/"]
     */
    public function testNumberOfGroupsInvalid(string $line): void
    {
        $this->parser->pushLine($line);

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Number of Groups" should be unsigned integer.');
        $this->parser['numberOfGroups'];
    }

    /**
     * @testWith ["99,123456789,5,42/", 42]
     *           ["99,123456789,5,4200/", 4200]
     *           ["99,123456789,5,0/", 0]
     *           ["99,123456789,5,042/", 42]
     */
    public function testNumberOfRecordsdValid(string $line, int $expected): void
    {
        $this->parser->pushLine($line);
        $this->assertEquals($expected, $this->parser['numberOfRecords']);
    }

    public function testNumberOfRecordsdMissing(): void
    {
        $this->parser->pushLine('99,123456789,5,/');

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Number of Records" cannot be omitted.');
        $this->parser['numberOfRecords'];
    }

    /**
     * @testWith ["99,123456789,5,-42/"]
     *           ["99,123456789,5,+42/"]
     *           ["99,123456789,5, 42/"]
     *           ["99,123456789,5,42 /"]
     *           ["99,123456789,5,a42/"]
     *           ["99,123456789,5,42b/"]
     *           ["99,123456789,5,41_000/"]
     *           ["99,123456789,5,41+000/"]
     */
    public function testNumberOfRecordsdInvalid(string $line): void
    {
        $this->parser->pushLine($line);

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Number of Records" should be unsigned integer.');
        $this->parser['numberOfRecords'];
    }

}
