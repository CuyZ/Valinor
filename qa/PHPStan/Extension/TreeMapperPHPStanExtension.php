<?php

declare(strict_types=1);

namespace CuyZ\Valinor\QA\PHPStan\Extension;

use CuyZ\Valinor\Mapper\TreeMapper;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\PhpDoc\TypeStringResolver;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Generic\GenericClassStringType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;

final class TreeMapperPHPStanExtension implements DynamicMethodReturnTypeExtension
{
    private TypeStringResolver $resolver;

    public function __construct(TypeStringResolver $resolver)
    {
        $this->resolver = $resolver;
    }

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
        $argument = $methodCall->getArgs()[0]->value;
        $type = $scope->getType($argument);

        if ($type instanceof UnionType) {
            return $type->traverse(fn (Type $type) => $this->type($type));
        }

        return $this->type($type);
    }

    private function type(Type $type): Type
    {
        if ($type instanceof GenericClassStringType) {
            return $type->getGenericType();
        }

        if ($type instanceof ConstantStringType) {
            return $this->resolver->resolve($type->getValue());
        }

        return new MixedType();
    }
}
