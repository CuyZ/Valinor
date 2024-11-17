<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Normalizer;

use Attribute;
use CuyZ\Valinor\Normalizer\AsTransformer;
use CuyZ\Valinor\Normalizer\Format;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;

use function strtoupper;

final class AsTransformerAttributeTest extends IntegrationTestCase
{
    public function test_property_transformer_attribute_registered_with_attribute_is_used(): void
    {
        $class = new class ('foo') {
            public function __construct(
                #[TransformerAttributeForString]
                public string $value,
            ) {}
        };

        $result = $this->mapperBuilder()
            ->normalizer(Format::array())
            ->normalize($class);

        self::assertSame(['value' => 'FOO'], $result);
    }

    public function test_class_transformer_attribute_registered_with_attribute_is_used(): void
    {
        $class = new #[TransformerAttributeForObject] class ('foo') {
            public function __construct(
                public string $value,
            ) {}
        };

        $result = $this->mapperBuilder()
            ->normalizer(Format::array())
            ->normalize($class);

        self::assertSame(['value' => 'foo', 'added' => 'foo'], $result);
    }

    public function test_property_key_transformer_attribute_registered_with_attribute_is_used(): void
    {
        $class = new class ('foo') {
            public function __construct(
                #[TransformerAttributeForStringKey]
                public string $value,
            ) {}
        };

        $result = $this->mapperBuilder()
            ->normalizer(Format::array())
            ->normalize($class);

        self::assertSame(['VALUE' => 'foo'], $result);
    }
}

#[AsTransformer, Attribute(Attribute::TARGET_PROPERTY)]
final class TransformerAttributeForString
{
    public function normalize(string $value): string
    {
        return strtoupper($value);
    }
}

#[AsTransformer, Attribute(Attribute::TARGET_CLASS)]
final class TransformerAttributeForObject
{
    /**
     * @param callable(): array<mixed> $next
     * @return array<mixed>
     */
    public function normalize(object $object, callable $next): array
    {
        return $next() + ['added' => 'foo'];
    }
}

#[AsTransformer, Attribute(Attribute::TARGET_PROPERTY)]
final class TransformerAttributeForStringKey
{
    public function normalizeKey(string $value): string
    {
        return strtoupper($value);
    }
}
