<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Cache\Warmup;

use CuyZ\Valinor\Cache\Exception\InvalidSignatureToWarmup;
use CuyZ\Valinor\Definition\Repository\ClassDefinitionRepository;
use CuyZ\Valinor\Mapper\Object\Factory\ObjectBuilderFactory;
use CuyZ\Valinor\Mapper\Tree\Builder\InterfaceInferringContainer;
use CuyZ\Valinor\Type\ClassType;
use CuyZ\Valinor\Type\Parser\TypeParser;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\InterfaceType;
use CuyZ\Valinor\Type\Types\UnresolvableType;
use CuyZ\Valinor\Utility\TypeHelper;

use function in_array;

/** @internal */
final class RecursiveCacheWarmupService
{
    /** @var list<class-string> */
    private array $classesWarmedUp = [];

    public function __construct(
        private TypeParser $parser,
        private InterfaceInferringContainer $interfaceInferringContainer,
        private ClassDefinitionRepository $classDefinitionRepository,
        private ObjectBuilderFactory $objectBuilderFactory
    ) {}

    public function warmup(string ...$signatures): void
    {
        foreach ($signatures as $signature) {
            $type = $this->parser->parse($signature);

            if ($type instanceof UnresolvableType) {
                throw new InvalidSignatureToWarmup($type);
            }

            $this->warmupType($type);
        }
    }

    private function warmupType(Type $type): void
    {
        if ($type instanceof InterfaceType) {
            $this->warmupInterfaceType($type);
        }

        if ($type instanceof ClassType) {
            $this->warmupClassType($type);
        }

        foreach (TypeHelper::traverseRecursively($type) as $subType) {
            $this->warmupType($subType);
        }
    }

    private function warmupInterfaceType(InterfaceType $type): void
    {
        $interfaceName = $type->className();

        if (! $this->interfaceInferringContainer->has($interfaceName)) {
            return;
        }

        $function = $this->interfaceInferringContainer->inferFunctionFor($interfaceName);

        $this->warmupType($function->returnType);

        foreach ($function->parameters as $parameter) {
            $this->warmupType($parameter->type);
        }
    }

    private function warmupClassType(ClassType $type): void
    {
        if (in_array($type->className(), $this->classesWarmedUp, true)) {
            return;
        }

        $this->classesWarmedUp[] = $type->className();

        $classDefinition = $this->classDefinitionRepository->for($type);
        $objectBuilders = $this->objectBuilderFactory->for($classDefinition);

        foreach ($objectBuilders as $builder) {
            foreach ($builder->describeArguments() as $argument) {
                $this->warmupType($argument->type());
            }
        }
    }
}
