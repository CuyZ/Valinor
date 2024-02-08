<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fixture\Object;

use CuyZ\Valinor\Tests\Fixture\Enum\BackedIntegerEnum;

class ObjectWithConstants
{
    final public const CONST_WITH_STRING_VALUE_A = 'some string value';

    final public const CONST_WITH_STRING_VALUE_B = 'another string value';

    private const CONST_WITH_STRING_PRIVATE_VALUE = 'some private string value'; // @phpstan-ignore-line

    final public const CONST_WITH_PREFIX_WITH_STRING_VALUE = 'some prefixed string value';

    final public const CONST_WITH_INTEGER_VALUE_A = 1653398288;

    final public const CONST_WITH_INTEGER_VALUE_B = 1653398289;

    final public const CONST_WITH_FLOAT_VALUE_A = 1337.42;

    final public const CONST_WITH_FLOAT_VALUE_B = 404.512;

    final public const CONST_WITH_ENUM_VALUE_A = BackedIntegerEnum::FOO;

    final public const CONST_WITH_ENUM_VALUE_B = BackedIntegerEnum::BAR;

    final public const CONST_WITH_ARRAY_VALUE_A = [
        'string' => 'some string value',
        'integer' => 1653398288,
        'float' => 1337.42,
    ];

    final public const CONST_WITH_ARRAY_VALUE_B = [
        'string' => 'another string value',
        'integer' => 1653398289,
        'float' => 404.512,
    ];

    final public const CONST_WITH_NESTED_ARRAY_VALUE_A = [
        'nested_array' => [
            'string' => 'some string value',
            'integer' => 1653398288,
            'float' => 1337.42,
        ],
    ];

    final public const CONST_WITH_NESTED_ARRAY_VALUE_B = [
        'another_nested_array' => [
            'string' => 'another string value',
            'integer' => 1653398289,
            'float' => 404.512,
        ],
    ];
}
