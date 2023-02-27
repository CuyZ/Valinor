<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object\Factory;

use CuyZ\Valinor\Definition\ClassDefinition;
use CuyZ\Valinor\Definition\FunctionObject;
use CuyZ\Valinor\Definition\Repository\FunctionDefinitionRepository;
use CuyZ\Valinor\Mapper\Object\DateTimeFormatConstructor;
use CuyZ\Valinor\Mapper\Object\FunctionObjectBuilder;
use CuyZ\Valinor\Mapper\Object\NativeConstructorObjectBuilder;
use CuyZ\Valinor\Mapper\Object\ObjectBuilder;
use CuyZ\Valinor\Type\ClassType;
use DateTime;
use DateTimeImmutable;

use function array_filter;
use function count;

/** @internal */
final class DateTimeObjectBuilderFactory implements ObjectBuilderFactory
{
    public function __construct(
        private ObjectBuilderFactory $delegate,
        private FunctionDefinitionRepository $functionDefinitionRepository
    ) {
    }

    public function for(ClassDefinition $class): array
    {
        $className = $class->name();

        $builders = $this->delegate->for($class);

        if ($className !== DateTime::class && $className !== DateTimeImmutable::class) {
            return $builders;
        }

        // Remove `DateTime` & `DateTimeImmutable` native constructors
        $builders = array_filter($builders, fn (ObjectBuilder $builder) => ! $builder instanceof NativeConstructorObjectBuilder);

        $useDefaultBuilder = true;

        foreach ($builders as $builder) {
            if (count($builder->describeArguments()) === 1) {
                $useDefaultBuilder = false;
                // @infection-ignore-all
                break;
            }
        }

        if ($useDefaultBuilder) {
            // @infection-ignore-all / Ignore memoization
            $builders[] = $this->defaultBuilder($class->type());
        }

        return $builders;
    }

    private function defaultBuilder(ClassType $type): FunctionObjectBuilder
    {
        $constructor = new DateTimeFormatConstructor(DATE_ATOM, 'U');
        $function = new FunctionObject($this->functionDefinitionRepository->for($constructor), $constructor);

        return new FunctionObjectBuilder($function, $type);
    }
}
