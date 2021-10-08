<?php

namespace STS\Bai2\Parsers;

use PHPUnit\Framework\TestCase;

final class LineBufferWithPhysicalRecordLengthTest extends TestCase
{

    public function testConstructWithoutPhysicalRecordLengthSpecified()
    {
        $buffer = new LineBuffer('foo,bar,baz/');
        $this->assertNull($buffer->physicalRecordLength);
    }

    public function testConstructWithPhysicalRecordLengthSpecified()
    {
        $buffer = new LineBuffer('foo,bar,baz/', 80);
        $this->assertEquals(80, $buffer->physicalRecordLength);
    }

    public function testConstructThenSetPhysicalRecordLength()
    {
        $buffer = new LineBuffer('foo,bar,baz/');
        $buffer->physicalRecordLength = 80;
        $this->assertEquals(80, $buffer->physicalRecordLength);
    }

    // TODO(zmd): eat bounds checks input unpadded
    // TODO(zmd): eat bounds checks input partially padded
    // TODO(zmd): eat bounds checks input fully padded

    /* TODO(zmd): field access input unpadded
    public function testAccessFieldsInputUnpadded()
    {
        $buffer = new LineBuffer('foo,bar,baz/', 80);

        $this->assertEquals('foo', $buffer->field());
        $this->assertEquals('bar', $buffer->eat()->field());
        $this->assertEquals('baz', $buffer->eat()->field());
    }
    */
    // TODO(zmd): field access input partially padded
    // TODO(zmd): field access input fully padded

    // TODO(zmd): defaulted field access input unpadded
    // TODO(zmd): defaulted field access input partially padded
    // TODO(zmd): defaulted field access input fully padded

    // TODO(zmd): field bounds checks input unpadded
    // TODO(zmd): field bounds checks input partially padded
    // TODO(zmd): field bounds checks input fully padded

    // TODO(zmd): text field access input unpadded
    // TODO(zmd): text field access input partially padded
    // TODO(zmd): text field access input fully padded

    // TODO(zmd): defaulted field access input unpadded
    // TODO(zmd): defaulted field access input partially padded
    // TODO(zmd): defaulted field access input fully padded

    // TODO(zmd): text field bounds checks input unpadded
    // TODO(zmd): text field bounds checks input partially padded
    // TODO(zmd): text field bounds checks input fully padded

}
