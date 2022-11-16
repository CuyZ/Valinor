<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Fixture;

final class ObjectWithSubProperties
{
    public ObjectWithProperties $someValue;

    public ObjectWithProperties $someOtherValue;
}

final class ObjectWithProperties
{
    public string $someNestedValue = 'Schwifty!';

    public string $someOtherNestedValue = 'Schwifty!';
}
