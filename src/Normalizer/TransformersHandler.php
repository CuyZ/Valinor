<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer;

use CuyZ\Valinor\Definition\Attributes;
use CuyZ\Valinor\Definition\Repository\FunctionDefinitionRepository;
use CuyZ\Valinor\Normalizer\Exception\TransformerAttributeIsNotCallable;
use CuyZ\Valinor\Normalizer\Exception\TransformerHasInvalidCallableParameter;
use CuyZ\Valinor\Normalizer\Exception\TransformerHasNoParameter;
use CuyZ\Valinor\Normalizer\Exception\TransformerHasTooManyParameters;
use CuyZ\Valinor\Type\Types\CallableType;

use CuyZ\Valinor\Utility\Reflection\Reflection;

use function array_shift;
use function call_user_func;
use function count;
use function is_callable;

/** @internal */
final class TransformersHandler
{
    /** @var array<string, true> */
    private array $transformerCheck = [];

    public function __construct(
        private FunctionDefinitionRepository $functionDefinitionRepository,
        /** @var list<callable> */
        private array $transformers,
        /** @var list<class-string> */
        private array $transformerAttributes,
    ) {}

    public function transform(mixed $value, Attributes $attributes, callable $defaultTransformer): mixed
    {
        $filteredAttributes = [];

        foreach ($this->transformerAttributes as $transformerAttribute) {
            $filteredAttributes = [...$filteredAttributes, ...$attributes->ofType($transformerAttribute)];
        }

        return call_user_func(
            $this->next($this->transformers, $value, $filteredAttributes, $defaultTransformer),
        );
    }

    public function count(): int
    {
        return count($this->transformers) + count($this->transformerAttributes);
    }

    /**
     * @param list<callable> $transformers
     * @param array<object> $attributes
     */
    private function next(array $transformers, mixed $value, array $attributes, callable $defaultTransformer): callable
    {
        if ($attributes !== []) {
            return $this->nextAttribute(
                $value,
                $attributes,
                fn () => call_user_func($this->next($transformers, $value, [], $defaultTransformer)),
            );
        }

        $transformer = array_shift($transformers);

        if ($transformer === null) {
            return fn () => $defaultTransformer($value);
        }

        $this->checkTransformer($transformer);

        $parameters = $this->functionDefinitionRepository->for($transformer)->parameters();

        if (! $parameters->at(0)->type()->accepts($value)) {
            return $this->next($transformers, $value, [], $defaultTransformer);
        }

        return fn () => $transformer($value, fn () => call_user_func($this->next($transformers, $value, [], $defaultTransformer)));
    }

    /**
     * @param array<object> $attributes
     */
    private function nextAttribute(mixed $value, array $attributes, callable $next): callable
    {
        $attribute = array_shift($attributes);

        if ($attribute === null) {
            return $next;
        }

        if (! is_callable($attribute)) {
            throw new TransformerAttributeIsNotCallable($attribute::class);
        }

        $this->checkTransformer($attribute);

        $definition = $this->functionDefinitionRepository->for($attribute);

        if (! $definition->parameters()->at(0)->type()->accepts($value)) {
            return $this->nextAttribute($value, $attributes, $next);
        }

        return fn () => $attribute($value, fn () => call_user_func($this->nextAttribute($value, $attributes, $next)));
    }

    private function checkTransformer(callable $transformer): void
    {
        $key = Reflection::signature(Reflection::function($transformer));

        if (isset($this->transformerCheck[$key])) {
            return;
        }

        // @infection-ignore-all
        $this->transformerCheck[$key] = true;

        $function = $this->functionDefinitionRepository->for($transformer);

        $parameters = $function->parameters();

        if ($parameters->count() === 0) {
            throw new TransformerHasNoParameter($function);
        }

        if ($parameters->count() > 2) {
            throw new TransformerHasTooManyParameters($function);
        }

        if ($parameters->count() > 1 && ! $parameters->at(1)->type() instanceof CallableType) {
            throw new TransformerHasInvalidCallableParameter($function, $parameters->at(1)->type());
        }
    }
}
