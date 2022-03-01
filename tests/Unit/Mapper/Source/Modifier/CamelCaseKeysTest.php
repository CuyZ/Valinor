<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Mapper\Source\Modifier;

use CuyZ\Valinor\Mapper\Source\Modifier\CamelCaseKeys;
use PHPUnit\Framework\TestCase;

final class CamelCaseKeysTest extends TestCase
{
    public function test_replace_space(): void
    {
        $source = new CamelCaseKeys(['some key' => 'foo']);

        self::assertSame(['someKey' => 'foo'], iterator_to_array($source));
    }

    public function test_replace_dash(): void
    {
        $source = new CamelCaseKeys(['some-key' => 'foo']);

        self::assertSame(['someKey' => 'foo'], iterator_to_array($source));
    }

    public function test_replace_underscore(): void
    {
        $source = new CamelCaseKeys(['some_key' => 'foo']);

        self::assertSame(['someKey' => 'foo'], iterator_to_array($source));
    }

    public function test_root_path_is_mapped(): void
    {
        $source = new CamelCaseKeys(['level-one' => 'bar']);

        self::assertSame(['levelOne' => 'bar'], iterator_to_array($source));
    }

    public function test_sub_path_is_mapped(): void
    {
        $source = new CamelCaseKeys([
            'level-one' => [
                'level-two' => 'foo',
            ],
        ]);

        self::assertSame([
            'levelOne' => [
                'levelTwo' => 'foo',
            ],
        ], iterator_to_array($source));
    }

    public function test_root_iterable_path_is_mapped(): void
    {
        $source = new CamelCaseKeys([
            ['level-one' => 'foo'],
            ['level-one' => 'bar'],
        ]);

        self::assertSame([
            ['levelOne' => 'foo'],
            ['levelOne' => 'bar'],
        ], iterator_to_array($source));
    }

    public function test_sub_iterable_numeric_path_is_mapped(): void
    {
        $source = new CamelCaseKeys([
            'level-one' => [
                ['level-two' => 'bar'],
                ['level-two' => 'buz'],
            ],
        ]);

        self::assertSame([
            'levelOne' => [
                ['levelTwo' => 'bar'],
                ['levelTwo' => 'buz'],
            ],
        ], iterator_to_array($source));
    }

    public function test_sub_iterable_string_path_is_mapped(): void
    {
        $source = new CamelCaseKeys([
            'level-one' => [
                'level-two-a' => ['level-three' => 'bar'],
                'level-two-b' => ['level-three' => 'buz'],
            ],
        ]);

        self::assertSame([
            'levelOne' => [
                'levelTwoA' => ['levelThree' => 'bar'],
                'levelTwoB' => ['levelThree' => 'buz'],
            ],
        ], iterator_to_array($source));
    }

    public function test_path_with_sub_paths_are_mapped(): void
    {
        $source = new CamelCaseKeys([
            'level-one' => [
                ['level-two' => 'bar'],
                ['level-two' => 'buz'],
            ],
        ]);

        self::assertSame([
            'levelOne' => [
                ['levelTwo' => 'bar'],
                ['levelTwo' => 'buz'],
            ],
        ], iterator_to_array($source));
    }
}
