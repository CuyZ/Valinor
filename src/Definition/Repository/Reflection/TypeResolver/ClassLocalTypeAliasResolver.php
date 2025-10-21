<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Repository\Reflection\TypeResolver;

use CuyZ\Valinor\Type\ObjectType;
use CuyZ\Valinor\Type\ObjectWithGenericType;
use CuyZ\Valinor\Type\Parser\Factory\TypeParserFactory;
use CuyZ\Valinor\Type\Parser\VacantTypeAssignerParser;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\UnresolvableType;
use CuyZ\Valinor\Utility\Reflection\Annotations;

use function current;
use function key;
use function next;

/** @internal */
final class ClassLocalTypeAliasResolver
{
    private ClassGenericResolver $genericResolver;

    public function __construct(
        private TypeParserFactory $typeParserFactory,
    ) {
        $this->genericResolver = new ClassGenericResolver($this->typeParserFactory);
    }

    /**
     * @return array<non-empty-string, Type>
     */
    public function resolveLocalTypeAliases(ObjectType $type): array
    {
        $localAliases = $this->extractLocalAliasesFromDocBlock($type->className());

        if ($localAliases === []) {
            return [];
        }

        $vacantTypes = [];

        if ($type instanceof ObjectWithGenericType) {
            $vacantTypes = $this->genericResolver->resolveGenerics($type);
        }

        $typeParser = $this->typeParserFactory->buildAdvancedTypeParserForClass($type->className());
        $typeParser = new VacantTypeAssignerParser($typeParser, $vacantTypes);

        $types = [];

        foreach ($localAliases as $name => $raw) {
            $types[$name] = $typeParser->parse($raw);

            if ($types[$name] instanceof UnresolvableType) {
                $types[$name] = $types[$name]->forLocalAlias($raw, $name, $type);
            }
        }

        return $types;
    }

    /**
     * @param class-string $className
     * @return array<non-empty-string, non-empty-string>
     */
    private function extractLocalAliasesFromDocBlock(string $className): array
    {
        $aliases = [];

        $annotations = Annotations::forLocalAliases($className);

        foreach ($annotations as $annotation) {
            $tokens = $annotation->filtered();

            $name = current($tokens);
            $next = next($tokens);

            if ($next === '=') {
                next($tokens);
            }

            $key = key($tokens);

            // @phpstan-ignore notIdentical.alwaysTrue (Somehow PHPStan does not properly infer the key)
            if ($key !== null) {
                $aliases[$name] = $annotation->allAfter($key);
            }
        }

        return $aliases;
    }
}
