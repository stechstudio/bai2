<?php

namespace STS\Bai2\Tests\Parsers;

use PHPUnit\Framework\TestCase;

use STS\Bai2\Exceptions\InvalidUseException;
use STS\Bai2\Exceptions\InvalidRecordException;
use STS\Bai2\Exceptions\InvalidFieldNameException;

class RecordParserTestCase extends TestCase
{

    // TODO(zmd): once we have the record parser base class, use that as the type
    protected $parser;

    protected static string $parserClass;

    protected string $readableParserName;

    protected static string $fullRecordLine;

    protected static string $partialRecordLine;

    public function setUp(): void
    {
        $this->parser = new static::$parserClass();
    }

    // ===== common array access trait functionality ===========================

    public function testAccessFieldViaOffsetGet(): void
    {
        $this->parser->pushLine(static::$fullRecordLine);

        $this->assertEquals('01', $this->parser->offsetGet('recordCode'));
    }

    public function testOffsetGetThrowsOnUnknownField(): void
    {
        $this->parser->pushLine(static::$fullRecordLine);

        $this->expectException(InvalidFieldNameException::class);
        $this->expectExceptionMessage("{$this->readableParserName} does not have a \"fooBar\" field.");
        $this->parser->offsetGet('fooBar');
    }

    public function testOffsetGetThrowsIfNoLinesPushed(): void
    {
        $this->expectException(InvalidUseException::class);
        $this->expectExceptionMessage("Cannot parse {$this->readableParserName} without first pushing line(s).");
        $this->parser->offsetGet('recordCode');
    }

    public function testOffsetExistsThrowsIfNoLinesPushed(): void
    {
        $this->expectException(InvalidUseException::class);
        $this->expectExceptionMessage("Cannot parse {$this->readableParserName} without first pushing line(s).");
        $this->parser->offsetGet('recordCode');
    }

    public function testOffsetExistsForExtantField(): void
    {
        $this->parser->pushLine(static::$fullRecordLine);

        $this->assertTrue($this->parser->offsetExists('recordCode'));
    }

    public function testOffsetExistsForNonExtantField(): void
    {
        $this->parser->pushLine(static::$fullRecordLine);

        $this->assertFalse($this->parser->offsetExists('codedRecord'));
    }

    public function testOffsetSetAlwaysThrows(): void
    {
        $this->parser->pushLine(static::$fullRecordLine);

        $this->expectException(InvalidUseException::class);
        $this->expectExceptionMessage('::offsetSet() is unsupported.');
        $this->parser->offsetSet('codedRecord', '23');
    }

    public function testOffsetUnsetAlwaysThrows(): void
    {
        $this->parser->pushLine(static::$fullRecordLine);

        $this->expectException(InvalidUseException::class);
        $this->expectExceptionMessage('::offsetUnset() is unsupported.');
        $this->parser->offsetUnset('codedRecord');
    }

    public function testAccessFieldAsIfFromArray(): void
    {
        $this->parser->pushLine(static::$fullRecordLine);

        $this->assertEquals('01', $this->parser['recordCode']);
    }

    // ===== common record parser usage and validations ========================

    public function testToArrayThrowsIfNoLinesPushed(): void
    {
        $this->expectException(InvalidUseException::class);
        $this->expectExceptionMessage("Cannot parse {$this->readableParserName} without first pushing line(s).");
        $this->parser->toArray();
    }

    /**
     * @testWith ["18,nope,nope,nope/"]
     *           ["This ain't no header line!"]
     */
    public function testPushLineRejectsInvalidHeaderLine(string $invalidHeader): void
    {
        $this->expectException(InvalidRecordException::class);
        $this->expectExceptionMessage("Encountered an invalid or malformed {$this->readableParserName} record.");
        $this->parser->pushLine($invalidHeader);
    }

    /**
     * @testWith ["23,This ain't no continuation line!"]
     *           ["This ain't no continuation line!"]
     */
    public function testPushLineRejectsInvalidContinuationLine(string $invalidContinuation): void
    {
        $this->parser->pushLine(static::$partialRecordLine);

        $this->expectException(InvalidRecordException::class);
        $this->expectExceptionMessage("Encountered an invalid or malformed {$this->readableParserName} continuation.");
        $this->parser->pushLine($invalidContinuation);
    }

}
