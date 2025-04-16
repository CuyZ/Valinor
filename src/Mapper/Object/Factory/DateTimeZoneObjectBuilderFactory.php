<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object\Factory;

use CuyZ\Valinor\Definition\ClassDefinition;
use CuyZ\Valinor\Definition\FunctionObject;
use CuyZ\Valinor\Definition\Repository\FunctionDefinitionRepository;
use CuyZ\Valinor\Mapper\Object\FunctionObjectBuilder;
use CuyZ\Valinor\Mapper\Object\NativeConstructorObjectBuilder;
use CuyZ\Valinor\Mapper\Object\ObjectBuilder;
use CuyZ\Valinor\Mapper\Tree\Message\MessageBuilder;
use CuyZ\Valinor\Type\ObjectType;
use DateTimeZone;
use Exception;

use function array_filter;
use function count;

/** @internal */
final class DateTimeZoneObjectBuilderFactory implements ObjectBuilderFactory
{
    private ObjectBuilderFactory $delegate;

    private FunctionDefinitionRepository $functionDefinitionRepository;

    public function __construct(ObjectBuilderFactory $delegate, FunctionDefinitionRepository $functionDefinitionRepository)
    {
        $this->delegate = $delegate;
        $this->functionDefinitionRepository = $functionDefinitionRepository;
    }

    public function for(ClassDefinition $class): array
    {
        $builders = $this->delegate->for($class);

        if ($class->name !== DateTimeZone::class) {
            return $builders;
        }

        // Remove `DateTimeZone` native constructors
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
            $builders[] = $this->defaultBuilder($class->type);
        }

        /** @var non-empty-list<ObjectBuilder> */
        return $builders;
    }

    private function defaultBuilder(ObjectType $type): FunctionObjectBuilder
    {
        $constructor = function (string $timezone) {
            try {
                return new DateTimeZone($timezone);
            } catch (Exception) {
                throw MessageBuilder::newError('Value {source_value} is not a valid timezone.')
                    ->withCode('invalid_timezone')
                    ->build();
            }
        };

        $function = new FunctionObject($this->functionDefinitionRepository->for($constructor), $constructor);

        return new FunctionObjectBuilder($function, $type);
    }
}
