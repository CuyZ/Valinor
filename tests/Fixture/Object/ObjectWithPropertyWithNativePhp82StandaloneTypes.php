<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fixture\Object;

// PHP8.2 move to anonymous class
final class ObjectWithPropertyWithNativePhp82StandaloneTypes
{
    public null $nativeNull = null;

    public true $nativeTrue; // @phpstan-ignore class.notFound (PHP8.2 remove this line)

    public false $nativeFalse;
}
