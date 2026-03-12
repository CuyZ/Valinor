<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Normalizer\CommonExamples;

use CuyZ\Valinor\Normalizer\Format;
use CuyZ\Valinor\Normalizer\Transformer\Common\SnakeCaseKeysTransformer;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;

final class ObjectKeysToSnakeCaseTest extends IntegrationTestCase
{
    public function test_object_keys_are_converted_to_snake_case(): void
    {
        $result = $this->normalizerBuilder()
            ->registerTransformer(new SnakeCaseKeysTransformer())
            ->normalizer(Format::array())
            ->normalize(new class () {
                public function __construct(
                    public string $userName = 'John Doe',
                    public string $LastName = 'Doe',
                    public string $emailAddress = 'john.doe@example.com',
                ) {}
            });

        self::assertSame([
            'user_name' => 'John Doe',
            'last_name' => 'Doe',
            'email_address' => 'john.doe@example.com',
        ], $result);
    }
}
