<?php

declare(strict_types=1);

namespace CuyZ\Valinor\QA\PHPStan\Extension;

use CuyZ\Valinor\Mapper\TreeMapper;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\PhpDoc\TypeStringResolver;
use PHPStan\PhpDocParser\Parser\ParserException;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;

use function count;
use function implode;
use function method_exists;

final class TreeMapperPHPStanExtension implements DynamicMethodReturnTypeExtension
{
    public function __construct(private TypeStringResolver $resolver) {}

    public function getClass(): string
    {
        return TreeMapper::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return $methodReflection->getName() === 'map';
    }

    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): Type
    {
        $arguments = $methodCall->getArgs();

        if (count($arguments) === 0) {
            return new MixedType();
        }

        $type = $scope->getType($arguments[0]->value);

        if ($type instanceof UnionType) {
            return $type->traverse(fn (Type $type) => $this->type($type));
        }

        try {
            return $this->type($type);
        } catch (ParserException) {
            // Fallback to `mixed` type if the type cannot be resolved. This can
            // occur with a type that is not understood/supported by PHPStan. If
            // that happens, returning a mixed type is the safest option, as it
            // will not make the analysis fail.
            return new MixedType();
        }
    }

    private function type(Type $type): Type
    {
        if ($type->isConstantValue()->yes()) {
            $value = implode('', $type->getConstantScalarValues());

            return $this->resolver->resolve($value);
        }

        // @phpstan-ignore function.alreadyNarrowedType (support for PHPStan v1)
        if (method_exists($type, 'isClassString') && $type->isClassString()->yes()) {
            return $type->getClassStringObjectType();
        }

        // @phpstan-ignore method.nonObject (support for PHPStan v1)
        if (method_exists($type, 'isClassStringType') && $type->isClassStringType()->yes()) {
            return $type->getClassStringObjectType();
        }

        return new MixedType();
    }
}
