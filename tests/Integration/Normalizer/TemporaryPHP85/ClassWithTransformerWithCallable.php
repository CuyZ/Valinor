<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Normalizer\TemporaryPHP85;

// PHP8.5 move to \CuyZ\Valinor\Tests\Integration\Normalizer\NormalizerCompiledCodeTest
#[TransformerAttributeWithCallable(static function (ClassWithTransformerWithCallable $object) {
    return $object->value . 'bar';
})]
final class ClassWithTransformerWithCallable
{
    public function __construct(
        public string $value,
    ) {}
}
