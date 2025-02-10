<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Mapper\Object;

use CuyZ\Valinor\Library\Settings;
use CuyZ\Valinor\Mapper\Object\Argument;
use CuyZ\Valinor\Mapper\Object\Arguments;
use CuyZ\Valinor\Mapper\Object\ArgumentsValues;
use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Type\Types\NativeClassType;
use CuyZ\Valinor\Type\Types\StringValueType;
use PHPUnit\Framework\TestCase;

final class ArgumentsValuesTest extends TestCase
{
    public function test_dynamic_properties_to_arguments(): void
    {
        $settings = new Settings();
        $settings->allowDynamicPropertiesFor = [\ArrayAccess::class];

        $shell = Shell::root(
            $settings,
            new NativeClassType(\ArrayAccess::class),
            [
                'foo' => 'fooExists',
                'dynamic' => 'baz',
            ]
        );
        $arguments = new Arguments(
            new Argument('foo', StringValueType::from('bar'))
        );
        $values = ArgumentsValues::forClass($arguments, $shell);

        self::assertSame(2, iterator_count($values->getIterator()));
        $argumentNames = [];
        foreach ($values->getIterator() as $value) {
            $argumentNames[] = $value->name();
        }
        self::assertSame(['foo', 'dynamic'], $argumentNames);
    }

    public function test_dynamic_properties_to_arguments_skip_on_non_objects(): void
    {
        $settings = new Settings();
        $settings->allowDynamicPropertiesFor = [\ArrayAccess::class];

        $shell = Shell::root(
            $settings,
            new StringValueType('test'),
            [
                'foo' => 'fooExists',
                'dynamic' => 'baz',
            ]
        );
        $arguments = new Arguments(
            new Argument('foo', StringValueType::from('bar'))
        );
        $values = ArgumentsValues::forClass($arguments, $shell);

        self::assertSame(1, iterator_count($values->getIterator()));
    }
}
