<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Repository\Reflection;

use CuyZ\Valinor\Definition\Attributes;
use CuyZ\Valinor\Definition\FunctionDefinition;
use CuyZ\Valinor\Definition\Parameters;
use CuyZ\Valinor\Definition\Repository\AttributesRepository;
use CuyZ\Valinor\Definition\Repository\FunctionDefinitionRepository;
use CuyZ\Valinor\Definition\Repository\Reflection\TypeResolver\FunctionReturnTypeResolver;
use CuyZ\Valinor\Definition\Repository\Reflection\TypeResolver\ReflectionTypeResolver;
use CuyZ\Valinor\Type\Parser\Factory\TypeParserFactory;
use CuyZ\Valinor\Type\Types\UnresolvableType;
use CuyZ\Valinor\Utility\Reflection\Reflection;
use ReflectionFunction;
use ReflectionParameter;

use function array_map;
use function str_ends_with;
use function str_starts_with;

/** @internal */
final class ReflectionFunctionDefinitionRepository implements FunctionDefinitionRepository
{
    private TypeParserFactory $typeParserFactory;

    private AttributesRepository $attributesRepository;

    private ReflectionParameterDefinitionBuilder $parameterBuilder;

    public function __construct(TypeParserFactory $typeParserFactory, AttributesRepository $attributesRepository)
    {
        $this->typeParserFactory = $typeParserFactory;
        $this->attributesRepository = $attributesRepository;
        $this->parameterBuilder = new ReflectionParameterDefinitionBuilder($attributesRepository);
    }

    public function for(callable $function): FunctionDefinition
    {
        $reflection = Reflection::function($function);

        $nativeParser = $this->typeParserFactory->buildNativeTypeParserForFunction($reflection);
        $advancedParser = $this->typeParserFactory->buildAdvancedTypeParserForFunction($reflection);

        $typeResolver = new ReflectionTypeResolver($nativeParser, $advancedParser);

        $returnTypeResolver = new FunctionReturnTypeResolver($typeResolver);

        $parameters = array_map(
            fn (ReflectionParameter $parameter) => $this->parameterBuilder->for($parameter, $typeResolver),
            $reflection->getParameters(),
        );

        $name = $reflection->getName();
        $signature = $this->signature($reflection);
        $class = $reflection->getClosureScopeClass();
        $returnType = $returnTypeResolver->resolveReturnTypeFor($reflection);
        $nativeReturnType = $returnTypeResolver->resolveNativeReturnTypeFor($reflection);
        // PHP8.2 use `ReflectionFunction::isAnonymous()`
        $isClosure = $name === '{closure}' || str_ends_with($name, '\\{closure}') || str_starts_with($name, '{closure:');

        if ($returnType instanceof UnresolvableType) {
            $returnType = $returnType->forFunctionReturnType($signature);
        } elseif (! $returnType->matches($nativeReturnType)) {
            $returnType = UnresolvableType::forNonMatchingFunctionReturnTypes($name, $nativeReturnType, $returnType);
        }

        return new FunctionDefinition(
            $name,
            $signature,
            new Attributes(...$this->attributesRepository->for($reflection)),
            $reflection->getFileName() ?: null,
            $class?->name,
            $reflection->getClosureThis() === null,
            $isClosure,
            new Parameters(...$parameters),
            $returnType,
        );
    }

    /**
     * @return non-empty-string
     */
    private function signature(ReflectionFunction $reflection): string
    {
        // PHP8.2 use `ReflectionFunction::isAnonymous()`
        if ($reflection->name === '{closure}' || str_ends_with($reflection->name, '\\{closure}') || str_starts_with($reflection->name, '{closure:')) {
            $startLine = $reflection->getStartLine();
            $endLine = $reflection->getEndLine();

            return $startLine === $endLine
                ? "Closure (line $startLine of {$reflection->getFileName()})"
                : "Closure (lines $startLine to $endLine of {$reflection->getFileName()})";
        }

        return $reflection->getClosureScopeClass()
            ? $reflection->getClosureScopeClass()->name . '::' . $reflection->name . '()'
            : $reflection->name . '()';
    }
}
