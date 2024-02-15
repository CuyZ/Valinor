<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Normalizer\CommonExamples;

use Attribute;
use CuyZ\Valinor\Normalizer\Format;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;

final class UppercaseFromAttributeTest extends IntegrationTestCase
{
    public function test_uppercase_attribute_works_properly(): void
    {
        $result = $this->mapperBuilder()
            ->registerTransformer(Uppercase::class)
            ->normalizer(Format::array())
            ->normalize(new class () {
                public function __construct(
                    #[Uppercase]
                    public string $value = 'Some value',
                ) {}
            });

        self::assertSame([
            'value' => 'SOME VALUE',
        ], $result);
    }
}

#[Attribute(Attribute::TARGET_PROPERTY)]
final class Uppercase
{
    public function normalize(string $value, callable $next): string
    {
        return strtoupper((string)$next());
    }
}
