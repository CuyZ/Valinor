<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Mapper\Source;

use CuyZ\Valinor\Mapper\Source\Exception\InvalidJson;
use CuyZ\Valinor\Mapper\Source\Exception\SourceNotIterable;
use CuyZ\Valinor\Mapper\Source\JsonSource;
use PHPUnit\Framework\TestCase;

use function iterator_to_array;

final class JsonSourceTest extends TestCase
{
    public function test_valid_json_is_parsed_correctly(): void
    {
        $source = new JsonSource('{"foo": "bar"}');

        self::assertSame(['foo' => 'bar'], iterator_to_array($source));
    }

    public function test_invalid_json_throws_exception(): void
    {
        try {
            new JsonSource('some invalid JSON entry');

            self::fail();
        } catch (InvalidJson $exception) {
            self::assertSame(1566307185, $exception->getCode());
            self::assertSame('Invalid JSON source.', $exception->getMessage());
            self::assertSame('some invalid JSON entry', $exception->source());
        }
    }

    public function test_invalid_json_type_throws_exception(): void
    {
        try {
            new JsonSource('true');

            self::fail();
        } catch (SourceNotIterable $exception) {
            self::assertSame(1566307291, $exception->getCode());
            self::assertSame('Invalid source, expected an iterable.', $exception->getMessage());
            self::assertSame('true', $exception->source());
        }
    }
}
