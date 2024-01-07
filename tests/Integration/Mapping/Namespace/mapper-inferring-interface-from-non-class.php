<?php

namespace SimpleNamespace;

use CuyZ\Valinor\MapperBuilder;

require_once __DIR__ . '/../../../../vendor/autoload.php';

interface SomeInterface {}

final class ImplementationOne implements SomeInterface {}

final class ImplementationTwo implements SomeInterface {}

return (new MapperBuilder())
    ->infer(
        SomeInterface::class,
        /** @return class-string<ImplementationOne|ImplementationTwo> */
        static fn (string $type): string => match ($type) {
            'one' => ImplementationOne::class,
            'two' => ImplementationTwo::class,
            default => throw new \RuntimeException(),
        },
    )
    ->mapper()
    ->map(
        SomeInterface::class,
        ['type' => 'one'],
    );
