<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Normalizer\CommonExamples;

use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Normalizer\Format;
use PHPUnit\Framework\TestCase;

final class ObjectKeysToSnakeCaseTest extends TestCase
{
    public function test_object_keys_are_converted_to_snake_case(): void
    {
        $result = (new MapperBuilder())
            ->registerTransformer(
                function (object $object, callable $next) {
                    $result = [];

                    foreach ($next() as $key => $value) {
                        $newKey = strtolower(preg_replace('/[A-Z]/', '_$0', lcfirst((string)$key)) ?? '');

                        $result[$newKey] = $value;
                    }

                    return $result;
                },
            )
            ->normalizer(Format::array())
            ->normalize(new class () {
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
