<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Definition\Repository\FunctionDefinitionRepository;
use CuyZ\Valinor\Mapper\Http\HttpRequest;
use CuyZ\Valinor\Mapper\Tree\Exception\KeyConverterHasInvalidStringParameter;
use CuyZ\Valinor\Mapper\Tree\Exception\KeyConverterHasNoParameter;
use CuyZ\Valinor\Mapper\Tree\Exception\KeyConverterHasTooManyParameters;
use CuyZ\Valinor\Mapper\Tree\Exception\KeysCollision;
use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use CuyZ\Valinor\Mapper\Tree\Message\Message;
use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Type\ObjectType;
use CuyZ\Valinor\Type\StringType;
use CuyZ\Valinor\Type\Types\ShapedArrayType;
use CuyZ\Valinor\Type\Types\UnresolvableType;

use Exception;
use Throwable;

use function array_key_exists;
use function is_array;
use function is_iterable;
use function iterator_to_array;

/** @internal */
final class KeyConverterNodeBuilder implements NodeBuilder
{
    private bool $convertersCallablesWereChecked = false;

    public function __construct(
        private NodeBuilder $delegate,
        private FunctionDefinitionRepository $functionDefinitionRepository,
        /** @var list<callable(string): string> */
        private array $converters,
        /** @var callable(Throwable): ErrorMessage */
        private mixed $exceptionFilter,
    ) {}

    public function build(Shell $shell): Node
    {
        if (! $this->shouldConvertKeys($shell)) {
            return $this->delegate->build($shell);
        }

        $this->checkConverterCallables();

        $value = $shell->value();

        if ($value instanceof HttpRequest) {
            [$routeValue, $routeNameMap, $routeKeyErrors] = $this->convert($value->routeParameters);
            [$queryValue, $queryNameMap, $queryKeyErrors] = $this->convert($value->queryParameters);
            [$bodyValue, $bodyNameMap, $bodyKeyErrors] = $this->convert($value->bodyValues);

            $newValue = new HttpRequest($routeValue, $queryValue, $bodyValue, $value->requestObject);
            $nameMap = $routeNameMap + $queryNameMap + $bodyNameMap;
            $keyErrors = [...$routeKeyErrors, ...$queryKeyErrors, ...$bodyKeyErrors];
        } elseif (! is_iterable($value)) {
            return $this->delegate->build($shell);
        } else {
            if (! is_array($value)) {
                $value = iterator_to_array($value);
            }

            [$newValue, $nameMap, $keyErrors] = $this->convert($value);
        }

        $errors = [];

        foreach ($keyErrors as $key => $error) {
            $errors[] = $shell
                ->child($key, UnresolvableType::forInvalidKey())
                ->error($error);
        }

        if ($errors !== []) {
            return $shell->errors($errors);
        }

        return $this->delegate->build(
            $shell->withValue($newValue)->withNameMap($nameMap),
        );
    }

    /**
     * @param array<mixed> $values
     * @return array{
     *     0: array<mixed>,
     *     1: array<string, string>,
     *     2: array<string, Message>,
     * }
     */
    private function convert(array $values): array
    {
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
                    $errors[(string)$key] = new KeysCollision($nameMap[$convertedKey], $convertedKey);
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

                $errors[(string)$key] = $exception;
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

    private function shouldConvertKeys(Shell $shell): bool
    {
        // Keys were already converted by a previous pass through this builder,
        // so we skip to avoid double-transformation.
        if ($shell->hasNameMap()) {
            return false;
        }

        return $shell->type instanceof ShapedArrayType
            || $shell->type instanceof ObjectType;
    }
}
