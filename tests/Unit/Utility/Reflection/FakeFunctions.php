<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Utility\Reflection;

return [
    'function_on_one_line' => fn () => 'foo',
    'function_on_several_lines' => function (string $foo, string $bar): string {
        if ($foo === 'foo') {
            return 'foo';
        }

        return $bar;
    },
];
