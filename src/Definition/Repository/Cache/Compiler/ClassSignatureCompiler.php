<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Repository\Cache\Compiler;

use CuyZ\Valinor\Definition\ClassSignature;

/** @internal */
final class ClassSignatureCompiler
{
    private TypeCompiler $typeCompiler;

    public function __construct(TypeCompiler $typeCompiler)
    {
        $this->typeCompiler = $typeCompiler;
    }

    public function compile(ClassSignature $signature): string
    {
        $types = '';

        foreach ($signature->generics() as $name => $type) {
            $types .= "'$name' => " . $this->typeCompiler->compile($type) . ',';
        }

        $class = ClassSignature::class;

        return "new $class({$signature->className()}::class, [$types])";
    }
}
