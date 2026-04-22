<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Mapper\Tree\Builder;

use CuyZ\Valinor\Definition\Attributes;
use CuyZ\Valinor\Mapper\Tree\Builder\KeyConverterNodeBuilder;
use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Tests\Fake\Definition\Repository\FakeFunctionDefinitionRepository;
use CuyZ\Valinor\Tests\Fake\Mapper\Tree\Builder\FakeNodeBuilder;
use CuyZ\Valinor\Tests\Unit\UnitTestCase;
use CuyZ\Valinor\Type\Dumper\TypeDumper;
use CuyZ\Valinor\Type\Types\ShapedArrayType;
use Throwable;

final class KeyConverterNodeBuilderTest extends UnitTestCase
{
    public function test_converter_callables_are_checked_only_once(): void
    {
        $functionDefinitionRepository = new FakeFunctionDefinitionRepository();

        $builder = new KeyConverterNodeBuilder(
            new FakeNodeBuilder(),
            $functionDefinitionRepository,
            [
                fn (string $key): string => $key,
                fn (string $key): string => $key,
            ],
            static fn (Throwable $error) => throw $error,
        );

        $builder->build($this->shell(['foo' => 'bar']));
        $builder->build($this->shell(['foo' => 'bar']));

        self::assertSame(2, $functionDefinitionRepository->callCount);
    }

    /**
     * @param array<mixed> $value
     */
    private function shell(array $value): Shell
    {
        return new Shell(
            name: '',
            path: '*root*',
            type: new ShapedArrayType([]),
            hasValue: true,
            value: $value,
            attributes: Attributes::empty(),
            allowScalarValueCasting: false,
            allowNonSequentialList: false,
            allowUndefinedValues: false,
            allowSuperfluousKeys: true,
            allowPermissiveTypes: false,
            allowedSuperfluousKeys: [],
            shouldApplyConverters: true,
            nodeBuilder: new FakeNodeBuilder(),
            typeDumper: $this->getService(TypeDumper::class),
            childrenCount: 0,
        );
    }
}
