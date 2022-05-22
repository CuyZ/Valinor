<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Attribute;

use Attribute;
use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Mapper\Tree\Visitor\ShellVisitor;
use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Tests\Integration\IntegrationTest;
use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;

final class ShellVisitorMappingTest extends IntegrationTest
{
    public function test_shell_visitor_attributes_are_called_during_mapping(): void
    {
        try {
            $result = (new MapperBuilder())->enableLegacyDoctrineAnnotations()->mapper()->map(
                ObjectWithShellVisitorAttributes::class,
                [
                    'valueA' => 'foo',
                    'valueB' => 'foo',
                ]
            );
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('bar', $result->valueA);
        self::assertSame('baz', $result->valueB);
    }
}

/**
 * @Annotation
 * @NamedArgumentConstructor
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER | Attribute::IS_REPEATABLE)]
final class ValueModifierAttribute implements ShellVisitor
{
    private string $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function visit(Shell $shell): Shell
    {
        return $shell->withValue($this->value);
    }
}

final class ObjectWithShellVisitorAttributes
{
    /** @ValueModifierAttribute(value="bar") */
    #[ValueModifierAttribute('bar')]
    public string $valueA = 'Schwifty!';

    /**
     * @ValueModifierAttribute(value="bar")
     * @ValueModifierAttribute(value="baz")
     */
    #[ValueModifierAttribute('bar')]
    #[ValueModifierAttribute('baz')]
    public string $valueB = 'Schwifty!';
}
