<?php

namespace STS\Bai2;

use STS\Bai2\Records\FileRecord;

trait ReadableNameTrait
{

    /**
     * Convert the receiving object's class name to something more readable.
     *
     * Meant to aid in the construction of human-friendly error messages in a
     * DRYer way.
     */
    protected function readableClassName(): string
    {
        $nameComponents = explode('\\', static::class);
        $nameSansParser = preg_replace('/Parser$/', '', end($nameComponents));

        // "FooBarBaz" -> ['F', 'oo', 'B', 'ar', 'B', 'az']
        $components = preg_split(
            '/([A-Z])/',
            $nameSansParser,
            flags: PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY
        );

        // ['F', 'oo', 'B', 'ar', 'B', 'az'] -> [['F', 'oo'], ['B', 'ar'], ['B', 'az']]
        $chunked = array_chunk($components, 2);

        // [['F', 'oo'], ['B', 'ar'], ['B', 'az']] -> ['Foo', 'Bar', 'Baz']
        $words = array_map(fn ($chunk) => implode('', $chunk), $chunked);

        // ['Foo', 'Bar', 'Baz'] -> 'Foo Bar Baz'
        return implode(' ', $words);
    }

}
