<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fixture\Object\InterfaceWithPropertyHooks;

// The missing `@extends` tag on a generic parent is the very case under test.
// @phpstan-ignore missingType.generics
interface ChildInterfaceWithGenericParentAndNoExtendTag extends GenericBaseInterface {}
