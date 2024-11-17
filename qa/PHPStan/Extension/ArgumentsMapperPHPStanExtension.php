<?php

declare(strict_types=1);

namespace CuyZ\Valinor\QA\PHPStan\Extension;

use CuyZ\Valinor\Mapper\ArgumentsMapper;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\Constant\ConstantArrayTypeBuilder;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;

use function count;

final class ArgumentsMapperPHPStanExtension implements DynamicMethodReturnTypeExtension
{
    public function getClass(): string
    {
        return ArgumentsMapper::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return $methodReflection->getName() === 'mapArguments';
    }

    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): Type
    {
        $arguments = $methodCall->getArgs();

        if (count($arguments) === 0) {
            return new MixedType();
        }

        $type = $scope->getType($arguments[0]->value);

        if (! $type->isCallable()->yes()) {
            return new MixedType();
        }

        $acceptors = $type->getCallableParametersAcceptors($scope);

        if (count($acceptors) !== 1) {
            return new MixedType();
        }

        $parameters = $acceptors[0]->getParameters();

        $builder = ConstantArrayTypeBuilder::createEmpty();

        foreach ($parameters as $parameter) {
            $builder->setOffsetValueType(new ConstantStringType($parameter->getName()), $parameter->getType(), $parameter->isOptional());
        }

        return $builder->getArray();
    }
}
