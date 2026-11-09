<?php

namespace A\B;

use A\B\A;

final class Bar
{
    public string $bar;
}

final class Foo
{
    public Bar $foo;
}

final class FooWithUnion
{
    public Bar|int $foo;
}

final class FooWithSelf
{
    public ?FooWithSelf $child;

    public function __construct(
        public string $bar,
        ?self $child = null,
    ) {
        $this->child = $child;
    }
}

final class FooWithStaticConstructor
{
    private function __construct(public string $bar) {}

    public static function create(string $bar): static
    {
        return new static($bar);
    }
}

final class FooWithDocBlockSelf
{
    /** @var list<self> */
    public array $children = [];

    public string $bar;
}

/**
 * @template T = string
 */
abstract class ParentBar
{
    /** @var T */
    public $bar;
}

final class ChildBar extends ParentBar {}
