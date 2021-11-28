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
        $this->expectException(InvalidJson::class);
        $this->expectExceptionCode(1566307185);
        $this->expectExceptionMessage('The given value is not a valid JSON entry.');

        new JsonSource('@');
    }

    public function test_invalid_json_type_throws_exception(): void
    {
        $this->expectException(SourceNotIterable::class);
        $this->expectExceptionCode(1566307291);
        $this->expectExceptionMessage('The configuration is not an iterable but of type `bool`.');

        new JsonSource('true');
    }
}
