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
use CuyZ\Valinor\Definition\Repository\Reflection\TypeResolver\TemplateResolver;
use CuyZ\Valinor\Type\Parser\Factory\TypeParserFactory;
use CuyZ\Valinor\Type\Parser\UnresolvableTypeFinderParser;
use CuyZ\Valinor\Type\Parser\VacantTypeAssignerParser;
use CuyZ\Valinor\Type\Types\UnresolvableType;
use CuyZ\Valinor\Utility\Reflection\Reflection;
use ReflectionFunction;
use ReflectionParameter;

use function array_map;

/** @internal */
final class ReflectionFunctionDefinitionRepository implements FunctionDefinitionRepository
{
    private TypeParserFactory $typeParserFactory;

    private AttributesRepository $attributesRepository;

    private ReflectionParameterDefinitionBuilder $parameterBuilder;

    private TemplateResolver $templateResolver;

    public function __construct(TypeParserFactory $typeParserFactory, AttributesRepository $attributesRepository)
    {
        $this->typeParserFactory = $typeParserFactory;
        $this->attributesRepository = $attributesRepository;
        $this->parameterBuilder = new ReflectionParameterDefinitionBuilder($attributesRepository);
        $this->templateResolver = new TemplateResolver();
    }

    public function for(callable $function): FunctionDefinition
    {
        $reflection = Reflection::function($function);
        $signature = $this->signature($reflection);

        $nativeParser = $this->typeParserFactory->buildNativeTypeParserForFunction($function);
        $advancedParser = $this->typeParserFactory->buildAdvancedTypeParserForFunction($function);

        $templates = $this->templateResolver->templatesFromDocBlock($reflection, $signature, $advancedParser);
        $advancedParser = new VacantTypeAssignerParser($advancedParser, $templates);
        $advancedParser = new UnresolvableTypeFinderParser($advancedParser);

        $typeResolver = new ReflectionTypeResolver($nativeParser, $advancedParser);

        $returnTypeResolver = new FunctionReturnTypeResolver($typeResolver);

        $parameters = array_map(
            fn (ReflectionParameter $parameter) => $this->parameterBuilder->for($parameter, $typeResolver),
            $reflection->getParameters(),
        );

        $name = $reflection->getName();
        $class = $reflection->getClosureScopeClass();
        $returnType = $returnTypeResolver->resolveReturnTypeFor($reflection);
        $nativeReturnType = $returnTypeResolver->resolveNativeReturnTypeFor($reflection);

        if ($returnType instanceof UnresolvableType) {
            $returnType = $returnType->forFunctionReturnType($signature);
        } elseif (! $returnType->matches($nativeReturnType)) {
            $returnType = UnresolvableType::forNonMatchingTypes($nativeReturnType, $returnType)->forFunctionReturnType($signature);
        }

        return (new FunctionDefinition(
            $name,
            $signature,
            new Attributes(...$this->attributesRepository->for($reflection)),
            $reflection->getFileName() ?: null,
            $class?->name,
            $reflection->getClosureThis() === null,
            $reflection->isAnonymous(),
            new Parameters(...$parameters),
            $returnType,
        ))->forCallable($function);
    }

    /**
     * @return non-empty-string
     */
    private function signature(ReflectionFunction $reflection): string
    {
        if ($reflection->isAnonymous()) {
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
