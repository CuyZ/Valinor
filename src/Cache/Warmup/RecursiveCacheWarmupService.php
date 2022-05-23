<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Cache\Warmup;

use CuyZ\Valinor\Cache\Exception\InvalidSignatureToWarmup;
use CuyZ\Valinor\Definition\Repository\ClassDefinitionRepository;
use CuyZ\Valinor\Type\CompositeType;
use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use CuyZ\Valinor\Type\Parser\TypeParser;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\ClassType;

use function in_array;

/** @internal */
final class RecursiveCacheWarmupService
{
    private TypeParser $parser;

    private ClassDefinitionRepository $classDefinitionRepository;

    /** @var list<class-string> */
    private array $classesWarmedUp = [];

    public function __construct(TypeParser $parser, ClassDefinitionRepository $classDefinitionRepository)
    {
        $this->parser = $parser;
        $this->classDefinitionRepository = $classDefinitionRepository;
    }

    public function warmup(string ...$signatures): void
    {
        foreach ($signatures as $signature) {
            try {
                $this->warmupType($this->parser->parse($signature));
            } catch (InvalidType $exception) {
                throw new InvalidSignatureToWarmup($signature, $exception);
            }
        }
    }

    private function warmupType(Type $type): void
    {
        if ($type instanceof ClassType) {
            $this->warmupClassType($type);
        }

        if ($type instanceof CompositeType) {
            foreach ($type->traverse() as $subType) {
                $this->warmupType($subType);
            }
        }
    }

    private function warmupClassType(ClassType $type): void
    {
        if (in_array($type->className(), $this->classesWarmedUp, true)) {
            return;
        }

        $this->classesWarmedUp[] = $type->className();

        $classDefinition = $this->classDefinitionRepository->for($type);

        foreach ($classDefinition->properties() as $property) {
            $this->warmupType($property->type());
        }

        foreach ($classDefinition->methods() as $method) {
            $this->warmupType($method->returnType());

            foreach ($method->parameters() as $parameter) {
                $this->warmupType($parameter->type());
            }
        }
    }
}
