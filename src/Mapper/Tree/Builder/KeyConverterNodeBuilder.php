<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Definition\Repository\FunctionDefinitionRepository;
use CuyZ\Valinor\Mapper\Http\HttpRequest;
use CuyZ\Valinor\Mapper\Tree\Exception\KeyConverterHasInvalidStringParameter;
use CuyZ\Valinor\Mapper\Tree\Exception\KeyConverterHasNoParameter;
use CuyZ\Valinor\Mapper\Tree\Exception\KeyConverterHasTooManyParameters;
use CuyZ\Valinor\Mapper\Tree\Exception\KeysCollision;
use CuyZ\Valinor\Mapper\Tree\Exception\SeveralAttributesMapToSameKey;
use CuyZ\Valinor\Mapper\Tree\Exception\UnexpectedKeyInSource;
use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use CuyZ\Valinor\Mapper\Tree\Message\Message;
use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Type\StringType;
use CuyZ\Valinor\Type\Types\ShapedArrayType;
use CuyZ\Valinor\Type\Types\ShapedListType;
use CuyZ\Valinor\Type\Types\UnresolvableType;
use Exception;
use Throwable;
use WeakMap;

use function array_diff_key;
use function array_key_exists;
use function array_keys;
use function assert;
use function is_array;
use function is_iterable;
use function is_string;
use function iterator_to_array;

/** @internal */
final class KeyConverterNodeBuilder implements NodeBuilder
{
    private bool $convertersCallablesWereChecked = false;

    /** @var WeakMap<ShapedArrayType|ShapedListType, array<array-key, string>> */
    private WeakMap $attributeSourcesCache;

    public function __construct(
        private NodeBuilder $delegate,
        private FunctionDefinitionRepository $functionDefinitionRepository,
        /** @var list<callable(string): string> */
        private array $converters,
        /** @var callable(Throwable): ErrorMessage */
        private mixed $exceptionFilter,
    ) {
        $this->attributeSourcesCache = new WeakMap();
    }

    public function build(Shell $shell): Node
    {
        assert($shell->type instanceof ShapedArrayType || $shell->type instanceof ShapedListType);

        $attributeSources = $this->attributeSourcesCache[$shell->type] ??= $this->resolveAttributeSources($shell->type);

        if ($this->converters === [] && $attributeSources === []) {
            return $this->delegate->build($shell);
        }

        if ($shell->hasNameMap()) {
            return $this->delegate->build($shell);
        }

        $this->checkConverterCallables();

        $value = $shell->value();

        if ($value instanceof HttpRequest) {
            [$routeValue, $routeNameMap, $routeKeyErrors] = $this->convert($value->routeParameters, $attributeSources);
            [$queryValue, $queryNameMap, $queryKeyErrors] = $this->convert($value->queryParameters, $attributeSources);
            [$bodyValue, $bodyNameMap, $bodyKeyErrors] = $this->convert($value->bodyValues, $attributeSources);

            $newValue = new HttpRequest($routeValue, $queryValue, $bodyValue, $value->requestObject);
            $nameMap = $routeNameMap + $queryNameMap + $bodyNameMap;
            $keyErrors = [...$routeKeyErrors, ...$queryKeyErrors, ...$bodyKeyErrors];
        } elseif (! is_iterable($value)) {
            return $this->delegate->build($shell);
        } else {
            if (! is_array($value)) {
                $value = iterator_to_array($value);
            }

            [$newValue, $nameMap, $keyErrors, $superfluousKeys] = $this->convert($value, $attributeSources);

            if (! $shell->allowSuperfluousKeys) {
                $superfluousKeys = array_diff_key($superfluousKeys, $shell->allowedSuperfluousKeys);

                foreach (array_keys($superfluousKeys) as $sourceKey) {
                    $keyErrors[$sourceKey] = new UnexpectedKeyInSource();
                }
            }
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
     * @param array<array-key, string> $attributeSources element key => source key
     * @return array{
     *     0: array<mixed>,
     *     1: array<array-key, string>,
     *     2: array<string, Message>,
     *     3: array<string, null>,
     * }
     */
    private function convert(array $values, array $attributeSources): array
    {
        $newValue = [];
        $nameMap = [];
        $errors = [];
        $superfluousKeys = [];

        foreach ($attributeSources as $elementKey => $sourceKey) {
            $nameMap[$elementKey] = $sourceKey;

            if (array_key_exists($sourceKey, $values)) {
                $newValue[$elementKey] = $values[$sourceKey];

                unset($values[$sourceKey]);
            }
        }

        foreach ($values as $key => $value) {
            $convertedKey = (string)$key;

            try {
                foreach ($this->converters as $converter) {
                    $convertedKey = $converter($convertedKey);
                }

                if (isset($attributeSources[$convertedKey])) {
                    $superfluousKeys[$convertedKey] = null;
                } elseif (array_key_exists($convertedKey, $nameMap)) {
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

        return [$newValue, $nameMap, $errors, $superfluousKeys];
    }

    /**
     * @return array<array-key, string> element key => source key
     */
    private function resolveAttributeSources(ShapedArrayType|ShapedListType $type): array
    {
        $sources = [];
        $claimedBy = [];

        foreach ($type->elements as $key => $element) {
            $sourceKey = null;

            foreach ($element->attributes() as $attribute) {
                if (! $attribute->class->methods->has('mapKey')) {
                    continue;
                }

                $sourceKey ??= $key;

                // @phpstan-ignore method.notFound (attribute has a `mapKey` method, checked above)
                $mappedKey = $attribute->instantiate()->mapKey($sourceKey);

                assert(is_string($mappedKey));

                $sourceKey = $mappedKey;
            }

            if ($sourceKey === null) {
                continue;
            }

            // Two elements resolving to the same source key is a configuration
            // error (independent of the source data), not a mapping error.
            if (isset($claimedBy[$sourceKey])) {
                throw new SeveralAttributesMapToSameKey($sourceKey, $claimedBy[$sourceKey], $key);
            }

            $claimedBy[$sourceKey] = $key;
            $sources[$key] = $sourceKey;
        }

        return $sources;
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
