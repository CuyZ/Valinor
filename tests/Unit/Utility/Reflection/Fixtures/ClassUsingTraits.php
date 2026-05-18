<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Utility\Reflection\Fixtures;

use CuyZ\Valinor\Tests\Unit\Definition\Repository\Cache\Compiler\ClassWithAttributeWithClosure as test;

final class ClassUsingTraits
{
    use TraitWithUseStatements2;
    use TraitWithUseStatements;
    public test $testMore;
}
