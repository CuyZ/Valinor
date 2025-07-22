<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Converter\CommonExamples;

use Attribute;
use CuyZ\Valinor\Mapper\AsConverter;
use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;

use function json_decode;

final class JsonDecodeFromAttributeMappingTest extends IntegrationTestCase
{
    public function test_can_use_json_decode_converter_attribute(): void
    {
        $class = new class () {
            /** @var array<scalar> */
            #[JsonDecode]
            public array $value;
        };

        try {
            $result = $this->mapperBuilder()
                ->registerConverter(JsonDecode::class)
                ->mapper()
                ->map($class::class, '{"foo": "bar", "baz": 42}');

            self::assertSame(['foo' => 'bar', 'baz' => 42], $result->value);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }
    }
}

#[Attribute, AsConverter]
final class JsonDecode
{
    public function map(string $value, callable $next): mixed
    {
        $decoded = json_decode($value, associative: true, flags: JSON_THROW_ON_ERROR);

        return $next($decoded);
    }
}
