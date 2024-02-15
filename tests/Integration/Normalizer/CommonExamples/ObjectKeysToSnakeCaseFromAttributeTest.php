<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Normalizer\CommonExamples;

use Attribute;
use CuyZ\Valinor\Normalizer\Format;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;

final class ObjectKeysToSnakeCaseFromAttributeTest extends IntegrationTestCase
{
    public function test_object_keys_are_converted_to_snake_case(): void
    {
        $result = $this->mapperBuilder()
            ->registerTransformer(SnakeCaseProperties::class)
            ->normalizer(Format::array())
            ->normalize(new #[SnakeCaseProperties] class () {
                public function __construct(
                    public string $userName = 'John Doe',
                    public string $emailAddress = 'john.doe@example.com',
                ) {}
            });

        self::assertSame([
            'user_name' => 'John Doe',
            'email_address' => 'john.doe@example.com',
        ], $result);
    }
}

#[Attribute(Attribute::TARGET_CLASS)]
final class SnakeCaseProperties
{
    /**
     * @return array<mixed>
     */
    public function normalize(object $object, callable $next): array
    {
        $result = [];

        foreach ($next() as $key => $value) {
            $newKey = strtolower(preg_replace('/[A-Z]/', '_$0', lcfirst((string)$key)) ?? '');

            $result[$newKey] = $value;
        }

        return $result;
    }
}
