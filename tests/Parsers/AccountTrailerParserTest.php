<?php

declare(strict_types=1);

namespace STS\Bai2\Parsers;

use STS\Bai2\Tests\Parsers\RecordParserTestCase;

use STS\Bai2\Exceptions\InvalidTypeException;

/**
 * @group RecordParserTests
 */
final class AccountTrailerParserTest extends RecordParserTestCase
{

    protected static string $parserClass = AccountTrailerParser::class;

    protected static string $readableParserName = 'Account Trailer';

    protected static string $recordCode = '49';

    // NOTE: this example record comes straight for spec, pg. 22
    protected static string $fullRecordLine = '49,18650000,3/';

    protected static string $partialRecordLine = '49,18650000/';

    protected static string $continuedRecordLine = '88,3/';

    // ----- record-specific parsing and usage ---------------------------------

    public function testParseFromSingleLine(): void
    {
        $this->parser->pushLine(self::$fullRecordLine);

        $this->assertEquals('49', $this->parser['recordCode']);
        $this->assertEquals(18650000, $this->parser['accountControlTotal']);
        $this->assertEquals(3, $this->parser['numberOfRecords']);
    }

    public function testParseFromMultipleLines(): void
    {
        $this->parser->pushLine(self::$partialRecordLine);
        $this->parser->pushLine(self::$continuedRecordLine);

        $this->assertEquals('49', $this->parser['recordCode']);
        $this->assertEquals(18650000, $this->parser['accountControlTotal']);
        $this->assertEquals(3, $this->parser['numberOfRecords']);
    }

    public function testToArray(): void
    {
        $this->parser->pushLine(self::$fullRecordLine);

        $this->assertEquals(
            [
                'recordCode' => '49',
                'accountControlTotal' => 18650000,
                'numberOfRecords' => 3,
            ],
            $this->parser->toArray()
        );
    }

    // ----- record-specific field validation ----------------------------------

    /**
     * @testWith ["49,18650000,3/", 18650000]
     *           ["49,+18650000,3/", 18650000]
     *           ["49,-18650000,3/", -18650000]
     *           ["49,00000001,3/", 1]
     *           ["49,0,3/", 0]
     *           ["49,1,3/", 1]
     */
    public function testAccountControlTotalValid(string $line, int $expected): void
    {
        $this->parser->pushLine($line);
        $this->assertEquals($expected, $this->parser['accountControlTotal']);
    }

    public function testAccountControlTotalMissing(): void
    {
        $this->parser->pushLine('49,,3/');

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Account Control Total" cannot be omitted.');
        $this->parser['accountControlTotal'];
    }

    /**
     * @testWith ["49, 18650000,3/"]
     *           ["49,18650000 ,3/"]
     *           ["49,a18650000,3/"]
     *           ["49,18650000b,3/"]
     *           ["49,18_650_000,3/"]
     *           ["49,18+650+000,3/"]
     */
    public function testAccountControlTotalInvalid(string $line): void
    {
        $this->parser->pushLine($line);

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Account Control Total" must be signed or unsigned integer.');
        $this->parser['accountControlTotal'];
    }

    /**
     * @testWith ["49,18650000,3/", 3]
     *           ["49,18650000,100/", 100]
     *           ["49,18650000,0/", 0]
     *           ["49,18650000,001/", 1]
     */
    public function testNumberOfRecordsValid(string $line, int $expected): void
    {
        $this->parser->pushLine($line);
        $this->assertEquals($expected, $this->parser['numberOfRecords']);
    }

    public function testNumberOfRecordsMissing(): void
    {
        $this->parser->pushLine('49,18650000,/');

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Number of Records" cannot be omitted.');
        $this->parser['numberOfRecords'];
    }

    /**
     * @testWith ["49,18650000,-3/"]
     *           ["49,18650000,+3/"]
     *           ["49,18650000, 3/"]
     *           ["49,18650000,3 /"]
     *           ["49,18650000,a3/"]
     *           ["49,18650000,3b/"]
     *           ["49,18650000,2_000/"]
     *           ["49,18650000,2+000/"]
     */
    public function testNumberOfRecordsInvalid(string $line): void
    {
        $this->parser->pushLine($line);

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Number of Records" should be unsigned integer.');
        $this->parser['numberOfRecords'];
    }

}
