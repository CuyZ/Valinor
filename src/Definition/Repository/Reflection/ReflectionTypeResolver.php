<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Repository\Reflection;

use CuyZ\Valinor\Definition\Exception\TypesDoNotMatch;
use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use CuyZ\Valinor\Type\Parser\TypeParser;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\UnresolvableType;
use CuyZ\Valinor\Utility\Reflection\Reflection;
use ReflectionFunctionAbstract;
use ReflectionParameter;
use ReflectionProperty;

/** @internal */
final class ReflectionTypeResolver
{
    public function __construct(
        private TypeParser $nativeParser,
        private TypeParser $advancedParser
    ) {
    }

    public function resolveType(\ReflectionProperty|\ReflectionParameter|\ReflectionFunctionAbstract $reflection): Type
    {
        $nativeType = $this->nativeType($reflection);
        $typeFromDocBlock = $this->typeFromDocBlock($reflection);

        if (! $nativeType && ! $typeFromDocBlock) {
            return MixedType::get();
        }

        if (! $nativeType) {
            /** @var Type $typeFromDocBlock */
            return $typeFromDocBlock;
        }

        if (! $typeFromDocBlock) {
            return $nativeType;
        }

        if (! $typeFromDocBlock instanceof UnresolvableType
            && ! $nativeType instanceof UnresolvableType
            && ! $typeFromDocBlock->matches($nativeType)
        ) {
            throw new TypesDoNotMatch($reflection, $typeFromDocBlock, $nativeType);
        }

        return $typeFromDocBlock;
    }

    private function typeFromDocBlock(\ReflectionProperty|\ReflectionParameter|\ReflectionFunctionAbstract $reflection): ?Type
    {
        $type = $reflection instanceof ReflectionFunctionAbstract
            ? Reflection::docBlockReturnType($reflection)
            : Reflection::docBlockType($reflection);

        if ($type === null) {
            return null;
        }

        return $this->parseType($type, $reflection, $this->advancedParser);
    }

    private function nativeType(\ReflectionProperty|\ReflectionParameter|\ReflectionFunctionAbstract $reflection): ?Type
    {
        $reflectionType = $reflection instanceof ReflectionFunctionAbstract
            ? $reflection->getReturnType()
            : $reflection->getType();

        if (! $reflectionType) {
            return null;
        }

        $type = Reflection::flattenType($reflectionType);

        if ($reflection instanceof ReflectionParameter && $reflection->isVariadic()) {
            $type .= '[]';
        }

        return $this->parseType($type, $reflection, $this->nativeParser);
    }

    private function parseType(string $raw, \ReflectionProperty|\ReflectionParameter|\ReflectionFunctionAbstract $reflection, TypeParser $parser): Type
    {
        try {
            return $parser->parse($raw);
        } catch (InvalidType $exception) {
            $raw = trim($raw);
            $signature = Reflection::signature($reflection);

            if ($reflection instanceof ReflectionProperty) {
                return UnresolvableType::forProperty($raw, $signature, $exception);
            }

            if ($reflection instanceof ReflectionParameter) {
                return UnresolvableType::forParameter($raw, $signature, $exception);
            }

            return UnresolvableType::forMethodReturnType($raw, $signature, $exception);
        }
    }
}
