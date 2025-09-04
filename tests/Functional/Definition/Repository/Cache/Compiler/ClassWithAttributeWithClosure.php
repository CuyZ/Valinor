<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Functional\Definition\Repository\Cache\Compiler;

use CuyZ\Valinor\Tests\Fixture\Attribute\AttributeWithClosure;

// PHP8.5 move to anonymous class
#[AttributeWithClosure(strtolower(...))]
final class ClassWithAttributeWithClosure {}
