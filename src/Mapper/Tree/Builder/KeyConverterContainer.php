<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Definition\FunctionDefinition;
use CuyZ\Valinor\Definition\MethodDefinition;
use CuyZ\Valinor\Definition\Repository\FunctionDefinitionRepository;
use CuyZ\Valinor\Mapper\Tree\Exception\KeyConverterHasInvalidStringParameter;
use CuyZ\Valinor\Mapper\Tree\Exception\KeyConverterHasNoParameter;
use CuyZ\Valinor\Mapper\Tree\Exception\KeyConverterHasTooManyParameters;
use CuyZ\Valinor\Type\StringType;

/** @internal */
final class KeyConverterContainer
{
    private bool $convertersCallablesWereChecked = false;

    public function __construct(
        private FunctionDefinitionRepository $functionDefinitionRepository,
        /** @var non-empty-list<callable(string): string> */
        private array $converters,
    ) {}

    /**
     * @return non-empty-list<callable(string): string>
     */
    public function converters(): array
    {
        if (! $this->convertersCallablesWereChecked) {
            $this->convertersCallablesWereChecked = true;

            foreach ($this->converters as $converter) {
                $function = $this->functionDefinitionRepository->for($converter);

                self::checkConverter($function);
            }
        }

        return $this->converters;
    }

    private static function checkConverter(MethodDefinition|FunctionDefinition $method): void
    {
        if ($method->parameters->count() === 0) {
            throw new KeyConverterHasNoParameter($method);
        }

        if ($method->parameters->count() > 1) {
            throw new KeyConverterHasTooManyParameters($method);
        }

        if (! $method->parameters->at(0)->nativeType instanceof StringType) {
            throw new KeyConverterHasInvalidStringParameter($method, $method->parameters->at(0)->nativeType);
        }
    }

}
