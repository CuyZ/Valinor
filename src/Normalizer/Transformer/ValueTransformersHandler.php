<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer;

use CuyZ\Valinor\Definition\FunctionDefinition;
use CuyZ\Valinor\Definition\Repository\FunctionDefinitionRepository;
use CuyZ\Valinor\Normalizer\Exception\TransformerHasInvalidCallableParameter;
use CuyZ\Valinor\Normalizer\Exception\TransformerHasNoParameter;
use CuyZ\Valinor\Normalizer\Exception\TransformerHasTooManyParameters;
use CuyZ\Valinor\Type\Types\CallableType;

use function array_shift;
use function call_user_func;
use function method_exists;

/** @internal */
final class ValueTransformersHandler
{
    /** @var array<string, true> */
    private array $transformerCheck = [];

    public function __construct(
        private FunctionDefinitionRepository $functionDefinitionRepository,
    ) {}

    /**
     * @param array<object> $attributes
     * @param list<callable> $transformers
     * @return array<mixed>|scalar|null
     */
    public function transform(mixed $value, array $attributes, array $transformers, callable $defaultTransformer): mixed
    {
        return call_user_func(
            $this->next($transformers, $value, $attributes, $defaultTransformer),
        );
    }

    /**
     * @param list<callable> $transformers
     * @param list<object> $attributes
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

        $function = $this->functionDefinitionRepository->for($transformer);

        $this->checkTransformer($function);

        if (! $function->parameters()->at(0)->type()->accepts($value)) {
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

        if (! method_exists($attribute, 'normalize')) {
            return $this->nextAttribute($value, $attributes, $next);
        }

        // PHP8.1 First-class callable syntax
        $function = $this->functionDefinitionRepository->for([$attribute, 'normalize']);

        $this->checkTransformer($function);

        if (! $function->parameters()->at(0)->type()->accepts($value)) {
            return $this->nextAttribute($value, $attributes, $next);
        }

        return fn () => $attribute->normalize($value, fn () => call_user_func($this->nextAttribute($value, $attributes, $next)));
    }

    private function checkTransformer(FunctionDefinition $function): void
    {
        if (isset($this->transformerCheck[$function->signature()])) {
            return;
        }

        // @infection-ignore-all
        $this->transformerCheck[$function->signature()] = true;

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
