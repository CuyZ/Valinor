<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type;

use UnitEnum;

/** @api */
interface EnumType extends ObjectType, ScalarType
{
    /**
     * @return class-string<UnitEnum>
     */
    public function className(): string;

    public function readableSignature(): string;
}
