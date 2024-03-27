<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Repository\Reflection;

use CuyZ\Valinor\Type\GenericType;
use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use CuyZ\Valinor\Type\Parser\TypeParser;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\ArrayKeyType;
use CuyZ\Valinor\Type\Types\ArrayType;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\UnresolvableType;
use CuyZ\Valinor\Utility\Reflection\DocParser;
use CuyZ\Valinor\Utility\Reflection\Reflection;
use ReflectionFunctionAbstract;
use ReflectionParameter;
use ReflectionProperty;

use function trim;

/** @internal */
final class ReflectionTypeResolver
{
    public function __construct(
        private TypeParser $nativeParser,
        private TypeParser $advancedParser,
    ) {}

    public function resolveType(ReflectionProperty|ReflectionParameter|ReflectionFunctionAbstract $reflection): Type
    {
        $nativeType = $this->resolveNativeType($reflection);
        $typeFromDocBlock = $this->typeFromDocBlock($reflection);

        if (! $typeFromDocBlock) {
            // When the type is a class, it may declare templates that must be
            // filled with generics. PHP does not handle generics natively, so
            // we need to make sure that no generics are left unassigned by
            // parsing the type again using the advanced parser.
            if ($nativeType instanceof GenericType) {
                $nativeType = $this->parseType($nativeType->toString(), $reflection, $this->advancedParser);
            }

            return $nativeType;
        }

        if ($typeFromDocBlock instanceof UnresolvableType) {
            return $typeFromDocBlock;
        }

        if (! $typeFromDocBlock->matches($nativeType)) {
            return UnresolvableType::forDocBlockTypeNotMatchingNative($reflection, $typeFromDocBlock, $nativeType);
        }

        return $typeFromDocBlock;
    }

    public function resolveNativeType(ReflectionProperty|ReflectionParameter|ReflectionFunctionAbstract $reflection): Type
    {
        $reflectionType = $reflection instanceof ReflectionFunctionAbstract
            ? $reflection->getReturnType()
            : $reflection->getType();

        if (! $reflectionType) {
            return MixedType::get();
        }

        $type = Reflection::flattenType($reflectionType);
        $type = $this->parseType($type, $reflection, $this->nativeParser);

        return $this->handleVariadicType($reflection, $type);
    }

    private function typeFromDocBlock(ReflectionProperty|ReflectionParameter|ReflectionFunctionAbstract $reflection): ?Type
    {
        if ($reflection instanceof ReflectionFunctionAbstract) {
            $type = DocParser::functionReturnType($reflection);
        } elseif ($reflection instanceof ReflectionProperty) {
            $type = DocParser::propertyType($reflection);
        } else {
            $type = null;

            if ($reflection->isPromoted()) {
                // @phpstan-ignore-next-line / parameter is promoted so class exists for sure
                $type = DocParser::propertyType($reflection->getDeclaringClass()->getProperty($reflection->name));
            }

            if ($type === null) {
                $type = DocParser::parameterType($reflection);
            }
        }

        if ($type === null) {
            return null;
        }

        $type = $this->parseType($type, $reflection, $this->advancedParser);

        return $this->handleVariadicType($reflection, $type);
    }

    private function parseType(string $raw, ReflectionProperty|ReflectionParameter|ReflectionFunctionAbstract $reflection, TypeParser $parser): Type
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

    private function handleVariadicType(ReflectionProperty|ReflectionParameter|ReflectionFunctionAbstract $reflection, Type $type): Type
    {
        if (! $reflection instanceof ReflectionParameter || ! $reflection->isVariadic()) {
            return $type;
        }

        return new ArrayType(ArrayKeyType::default(), $type);
    }
}
