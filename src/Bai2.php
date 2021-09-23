<?php
namespace STS\Bai2;

class Bai2
{

    public static $recordTypes = [
        '01' => 'File Header',
        '02' => 'Group Header',
        '03' => 'Account Identifier and Summary/Status',
        '16' => 'Transaction Detail',
        '88' => 'Continuation',
        '49' => 'Account Trailer',
        '98' => 'Group Trailer',
        '99' => 'File Trailer'
    ];

    public $records = [];

    public function parseLine(string $line): void
    {
        [$type, $rest] = explode(',', $line, 2);
        $recordType = self::$recordTypes[$type];
        $this->records[$recordType] ??= [];
        $this->records[$recordType][] = $rest;
    }

    public function toArray(): array
    {
        return $this->records;
    }

}
