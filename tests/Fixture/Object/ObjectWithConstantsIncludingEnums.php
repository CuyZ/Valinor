<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fixture\Object;

use CuyZ\Valinor\Tests\Fixture\Enum\BackedIntegerEnum;

final class ObjectWithConstantsIncludingEnums extends ObjectWithConstants
{
    public const CONST_WITH_ENUM_VALUE_A = BackedIntegerEnum::FOO;

    public const CONST_WITH_ENUM_VALUE_B = BackedIntegerEnum::BAR;
}
