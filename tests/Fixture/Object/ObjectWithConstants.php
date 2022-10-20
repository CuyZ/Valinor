<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fixture\Object;

class ObjectWithConstants
{
    public const CONST_WITH_STRING_VALUE_A = 'some string value';

    public const CONST_WITH_STRING_VALUE_B = 'another string value';

    private const CONST_WITH_STRING_PRIVATE_VALUE = 'some private string value'; // @phpstan-ignore-line

    public const CONST_WITH_PREFIX_WITH_STRING_VALUE = 'some prefixed string value';

    public const CONST_WITH_INTEGER_VALUE_A = 1653398288;

    public const CONST_WITH_INTEGER_VALUE_B = 1653398289;

    public const CONST_WITH_FLOAT_VALUE_A = 1337.42;

    public const CONST_WITH_FLOAT_VALUE_B = 404.512;

    public const CONST_WITH_ARRAY_VALUE_A = [
        'string' => 'some string value',
        'integer' => 1653398288,
        'float' => 1337.42,
    ];

    public const CONST_WITH_ARRAY_VALUE_B = [
        'string' => 'another string value',
        'integer' => 1653398289,
        'float' => 404.512,
    ];

    public const CONST_WITH_NESTED_ARRAY_VALUE_A = [
        'nested_array' => [
            'string' => 'some string value',
            'integer' => 1653398288,
            'float' => 1337.42,
        ],
    ];

    public const CONST_WITH_NESTED_ARRAY_VALUE_B = [
        'another_nested_array' => [
            'string' => 'another string value',
            'integer' => 1653398289,
            'float' => 404.512,
        ],
    ];

    /**
     * PHP8.1 replace all calls with `ObjectWithConstants::class`
     * @return class-string<self>
     */
    public static function className(): string
    {
        return PHP_VERSION_ID >= 8_01_00
            ? ObjectWithConstantsIncludingEnums::class
            : ObjectWithConstants::class;
    }
}
