<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Normalizer\TemporaryPHP85;

// PHP8.5 move to \CuyZ\Valinor\Tests\Integration\Normalizer\NormalizerCompiledCodeTest
final class ClassWithPropertyTransformerWithCallable
{
    public function __construct(
        #[TransformerAttributeWithCallable(static function (string $value) {
            return $value . 'baz';
        })]
        public string $value,
    ) {}
}
