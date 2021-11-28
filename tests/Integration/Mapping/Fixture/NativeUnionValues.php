<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Fixture;

// @PHP8.0 move inside \CuyZ\Valinor\Tests\Integration\Mapping\UnionValuesMappingTest
class NativeUnionValues
{
    public bool|float|int|string $scalarWithBoolean = 'Schwifty!';

    public bool|float|int|string $scalarWithFloat = 'Schwifty!';

    public bool|float|int|string $scalarWithInteger = 'Schwifty!';

    public bool|float|int|string $scalarWithString = 'Schwifty!';

    public string|null $nullableWithString = 'Schwifty!';

    public string|null $nullableWithNull = 'Schwifty!';
}

class NativeUnionValuesWithConstructor extends NativeUnionValues
{
    public function __construct(
        bool|float|int|string $scalarWithBoolean = 'Schwifty!',
        bool|float|int|string $scalarWithFloat = 'Schwifty!',
        bool|float|int|string $scalarWithInteger = 'Schwifty!',
        bool|float|int|string $scalarWithString = 'Schwifty!',
        string|null $nullableWithString = 'Schwifty!',
        string|null $nullableWithNull = 'Schwifty!'
    ) {
        $this->scalarWithBoolean = $scalarWithBoolean;
        $this->scalarWithFloat = $scalarWithFloat;
        $this->scalarWithInteger = $scalarWithInteger;
        $this->scalarWithString = $scalarWithString;
        $this->nullableWithString = $nullableWithString;
        $this->nullableWithNull = $nullableWithNull;
    }
}
