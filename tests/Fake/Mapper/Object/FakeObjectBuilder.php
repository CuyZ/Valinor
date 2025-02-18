<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fake\Mapper\Object;

use CuyZ\Valinor\Mapper\Object\Arguments;
use CuyZ\Valinor\Mapper\Object\ObjectBuilder;
use stdClass;

final class FakeObjectBuilder implements ObjectBuilder
{
    public function describeArguments(): Arguments
    {
        return new Arguments();
    }

    public function build(array $arguments): object
    {
        return new stdClass();
    }

    public function signature(): string
    {
        return 'FakeObjectBuilder';
    }
}
