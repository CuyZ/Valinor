<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Utility\Reflection;

return [
    'function_on_one_line' => fn () => 'foo',
    /** @noRector \Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector */
    'function_on_several_lines' => function (): string {
        return 'foo';
    },
];
