<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fixture\Object;

final class ObjectWithNestedObjectsProperties
{
    public ObjectWithNonEmptyStringProperty $nonEmptyString;
    public ObjectWithPositiveIntProperty $positiveInt;
    public ObjectWithNestedObjectProperty $objectWithNestedObject;
}
