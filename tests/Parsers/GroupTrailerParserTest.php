<?php

namespace STS\Bai2\Parsers;

use STS\Bai2\Tests\Parsers\RecordParserTestCase;

use STS\Bai2\Exceptions\InvalidTypeException;

/**
 * @group RecordParserTests
 */
final class GroupTrailerParserTest extends RecordParserTestCase
{

    protected static string $parserClass = GroupTrailerParser::class;

    protected static string $readableParserName = 'Group Trailer';

    protected static string $recordCode = '98';

    // NOTE: this example record comes straight for spec, pg. 23
    protected static string $fullRecordLine = '98,11800000,2,6/';

    protected static string $partialRecordLine = '98,11800000/';

    protected static string $continuedRecordLine = '88,2,6/';

    // ----- record-specific parsing and usage ---------------------------------

    public function testParseFromSingleLine(): void
    {
        $this->parser->pushLine(self::$fullRecordLine);

        $this->assertEquals('98', $this->parser['recordCode']);
        $this->assertEquals(11800000, $this->parser['groupControlTotal']);
        $this->assertEquals(2, $this->parser['numberOfAccounts']);
        $this->assertEquals(6, $this->parser['numberOfRecords']);
    }

    public function testParseFromMultipleLines(): void
    {
        $this->parser->pushLine(self::$partialRecordLine);
        $this->parser->pushLine(self::$continuedRecordLine);

        $this->assertEquals('98', $this->parser['recordCode']);
        $this->assertEquals(11800000, $this->parser['groupControlTotal']);
        $this->assertEquals(2, $this->parser['numberOfAccounts']);
        $this->assertEquals(6, $this->parser['numberOfRecords']);
    }

    public function testToArray(): void
    {
        $this->parser->pushLine(self::$fullRecordLine);

        $this->assertEquals(
            [
                'recordCode' => '98',
                'groupControlTotal' => 11800000,
                'numberOfAccounts' => 2,
                'numberOfRecords' => 6,
            ],
            $this->parser->toArray()
        );
    }

    // ----- record-specific field validation ----------------------------------

    /**
     * @testWith ["98,11800000,2,6/", 11800000]
     *           ["98,+11800000,2,6/", 11800000]
     *           ["98,-11800000,2,6/", -11800000]
     *           ["98,00000001,2,6/", 1]
     *           ["98,0,2,6/", 0]
     *           ["98,1,2,6/", 1]
     */
    public function testGroupControlTotalValid(
        string $line,
        int $expected
    ): void {
        $this->parser->pushLine($line);
        $this->assertEquals($expected, $this->parser['groupControlTotal']);
    }

    public function testGroupControlTotalMissing(): void
    {
        $this->parser->pushLine('98,,2,6/');

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Group Control Total" cannot be omitted.');
        $this->parser['groupControlTotal'];
    }

    /**
     * @testWith ["98, 11800000,2,6/"]
     *           ["98,11800000 ,2,6/"]
     *           ["98,a11800000,2,6/"]
     *           ["98,11800000b,2,6/"]
     *           ["98,11_800_000,2,6/"]
     *           ["98,11+800+000,2,6/"]
     */
    public function testGroupControlTotalInvalid(string $line): void
    {
        $this->parser->pushLine($line);

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Group Control Total" must be signed or unsigned integer.');
        $this->parser['groupControlTotal'];
    }

    /**
     * @testWith ["98,11800000,2,6/", 2]
     *           ["98,11800000,200,6/", 200]
     *           ["98,11800000,0,6/", 0]
     *           ["98,11800000,002,6/", 2]
     */
    public function testNumberOfAccountsValid(string $line, int $expected): void
    {
        $this->parser->pushLine($line);
        $this->assertEquals($expected, $this->parser['numberOfAccounts']);
    }

    public function testNumberOfAccountsMissing(): void
    {
        $this->parser->pushLine('98,11800000,,6/');

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Number of Accounts" cannot be omitted.');
        $this->parser['numberOfAccounts'];
    }

    /**
     * @testWith ["98,11800000,-2,6/"]
     *           ["98,11800000,+2,6/"]
     *           ["98,11800000, 2,6/"]
     *           ["98,11800000,2 ,6/"]
     *           ["98,11800000,a2,6/"]
     *           ["98,11800000,2b,6/"]
     *           ["98,11800000,1_000,6/"]
     *           ["98,11800000,1+000,6/"]
     */
    public function testNumberOfAccountsInvalid(string $line): void
    {
        $this->parser->pushLine($line);

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Number of Accounts" should be unsigned integer.');
        $this->parser['numberOfAccounts'];
    }

    /**
     * @testWith ["98,11800000,2,6/", 6]
     *           ["98,11800000,2,600/", 600]
     *           ["98,11800000,2,0/", 0]
     *           ["98,11800000,2,006/", 6]
     */
    public function testNumberOfRecordsValid(string $line, int $expected): void
    {
        $this->parser->pushLine($line);
        $this->assertEquals($expected, $this->parser['numberOfRecords']);
    }

    public function testNumberOfRecordsMissing(): void
    {
        $this->parser->pushLine('98,11800000,2,/');

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Number of Records" cannot be omitted.');
        $this->parser['numberOfRecords'];
    }

    /**
     * @testWith ["98,11800000,2,-6/"]
     *           ["98,11800000,2,+6/"]
     *           ["98,11800000,2, 6/"]
     *           ["98,11800000,2,6 /"]
     *           ["98,11800000,2,a6/"]
     *           ["98,11800000,2,6b/"]
     *           ["98,11800000,2,5_000/"]
     *           ["98,11800000,2,5+000/"]
     */
    public function testNumberOfRecordsInvalid(string $line): void
    {
        $this->parser->pushLine($line);

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Invalid field type: "Number of Records" should be unsigned integer.');
        $this->parser['numberOfRecords'];
    }

}
