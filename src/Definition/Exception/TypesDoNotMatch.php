<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Exception;

use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Utility\Reflection\Reflection;
use LogicException;
use ReflectionFunctionAbstract;
use ReflectionParameter;
use ReflectionProperty;
use Reflector;

/** @internal */
final class TypesDoNotMatch extends LogicException
{
    /**
     * @param ReflectionProperty|ReflectionParameter|ReflectionFunctionAbstract $reflection
     */
    public function __construct(Reflector $reflection, Type $typeFromDocBlock, Type $typeFromReflection)
    {
        $signature = Reflection::signature($reflection);

        if ($reflection instanceof ReflectionProperty) {
            $message = "Types for property `$signature` do not match: `{$typeFromDocBlock->toString()}` (docblock) does not accept `{$typeFromReflection->toString()}` (native).";
        } elseif ($reflection instanceof ReflectionParameter) {
            $message = "Types for parameter `$signature` do not match: `{$typeFromDocBlock->toString()}` (docblock) does not accept `{$typeFromReflection->toString()}` (native).";
        } else {
            $message = "Return types for method `$signature` do not match: `{$typeFromDocBlock->toString()}` (docblock) does not accept `{$typeFromReflection->toString()}` (native).";
        }

        parent::__construct($message, 1638471381);
    }
}
