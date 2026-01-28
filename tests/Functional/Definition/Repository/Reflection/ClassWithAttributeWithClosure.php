<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Functional\Definition\Repository\Reflection;

use CuyZ\Valinor\Tests\Fixture\Attribute\AttributeWithClosure;

// PHP8.5 move to \CuyZ\Valinor\Tests\Functional\Definition\Repository\Reflection\ReflectionAttributesRepositoryTest::test_attribute_with_closure_does_not_provide_arguments_to_attribute_definition
return new #[AttributeWithClosure(static function () {
    return 'foo';
})] class () {};
