<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Definition\Repository\FunctionDefinitionRepository;
use CuyZ\Valinor\Mapper\Tree\Exception\KeyConverterHasInvalidStringParameter;
use CuyZ\Valinor\Mapper\Tree\Exception\KeyConverterHasNoParameter;
use CuyZ\Valinor\Mapper\Tree\Exception\KeyConverterHasTooManyParameters;
use CuyZ\Valinor\Mapper\Tree\Exception\KeysCollision;
use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use CuyZ\Valinor\Mapper\Tree\Message\Message;
use CuyZ\Valinor\Type\StringType;
use Exception;
use Throwable;

/** @internal */
final class KeyConverterContainer
{
    private bool $convertersCallablesWereChecked = false;

    public function __construct(
        private FunctionDefinitionRepository $functionDefinitionRepository,
        /** @var non-empty-list<callable(string): string> */
        private array $converters,
        /** @var callable(Throwable): ErrorMessage */
        private mixed $exceptionFilter,
    ) {}

    public function hasConverters(): bool
    {
        return $this->converters !== [];
    }

    public function convert(array $values): array
    {
        $this->checkConverterCallables();

        $newValue = [];
        $nameMap = [];
        $errors = [];

        foreach ($values as $key => $value) {
            $convertedKey = (string)$key;

            try {
                foreach ($this->converters as $converter) {
                    $convertedKey = $converter($convertedKey);
                }

                if (array_key_exists($convertedKey, $nameMap)) {
                    $errors[$key] = new KeysCollision($nameMap[$convertedKey], $convertedKey);
                } else {
                    $newValue[$convertedKey] = $value;

                    if ($convertedKey !== (string)$key) {
                        $nameMap[$convertedKey] = (string)$key;
                    }
                }
            } catch (Exception $exception) {
                if (! $exception instanceof Message) {
                    $exception = ($this->exceptionFilter)($exception);
                }

                $errors[$key] = $exception;
            }
        }

        return [$newValue, $nameMap, $errors];
    }

    private function checkConverterCallables(): void
    {
        if ($this->convertersCallablesWereChecked) {
            return;
        }

        $this->convertersCallablesWereChecked = true;

        foreach ($this->converters as $converter) {
            $function = $this->functionDefinitionRepository->for($converter);

            if ($function->parameters->count() === 0) {
                throw new KeyConverterHasNoParameter($function);
            }

            if ($function->parameters->count() > 1) {
                throw new KeyConverterHasTooManyParameters($function);
            }

            if (! $function->parameters->at(0)->nativeType instanceof StringType) {
                throw new KeyConverterHasInvalidStringParameter($function, $function->parameters->at(0)->nativeType);
            }
        }
    }
}
