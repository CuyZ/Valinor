<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Definition\Repository\Reflection;

function function_on_one_line(): void {}

final class SomeClassWithOneMethod
{
    public static function method(): void {}
}

return [
    'function_on_one_line' => function_on_one_line(...),
    'class_static_method' => SomeClassWithOneMethod::method(...),
    'closure_on_one_line' => fn () => 'foo',
    'closure_on_several_lines' => function (string $foo, string $bar): string {
        if ($foo === 'foo') {
            return 'foo';
        }

        return $bar;
    },
];
