<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer;

use CuyZ\Valinor\Definition\AttributeDefinition;
use CuyZ\Valinor\Definition\FunctionDefinition;
use CuyZ\Valinor\Definition\MethodDefinition;
use CuyZ\Valinor\Definition\Repository\FunctionDefinitionRepository;
use CuyZ\Valinor\Normalizer\Exception\KeyTransformerHasTooManyParameters;
use CuyZ\Valinor\Normalizer\Exception\KeyTransformerParameterInvalidType;
use CuyZ\Valinor\Normalizer\Exception\TransformerHasInvalidCallableParameter;
use CuyZ\Valinor\Normalizer\Exception\TransformerHasNoParameter;
use CuyZ\Valinor\Normalizer\Exception\TransformerHasTooManyParameters;
use CuyZ\Valinor\Type\StringType;
use CuyZ\Valinor\Type\Types\CallableType;

/** @internal */
final class TransformerContainer
{
    private bool $transformersCallablesWereChecked = false;

    public function __construct(
        private FunctionDefinitionRepository $functionDefinitionRepository,
        /** @var list<callable> */
        private array $transformers,
    ) {}

    public function hasTransformers(): bool
    {
        return $this->transformers !== [];
    }

    /**
     * @return list<callable>
     */
    public function transformers(): array
    {
        if (! $this->transformersCallablesWereChecked) {
            $this->transformersCallablesWereChecked = true;

            foreach ($this->transformers as $transformer) {
                $function = $this->functionDefinitionRepository->for($transformer);

                self::checkTransformer($function);
            }
        }

        return $this->transformers;
    }

    public static function filterTransformerAttributes(AttributeDefinition $attribute): bool
    {
        return $attribute->class->methods->has('normalize')
            && self::checkTransformer($attribute->class->methods->get('normalize'));
    }

    public static function filterKeyTransformerAttributes(AttributeDefinition $attribute): bool
    {
        return $attribute->class->methods->has('normalizeKey')
            && self::checkKeyTransformer($attribute->class->methods->get('normalizeKey'));
    }

    private static function checkTransformer(MethodDefinition|FunctionDefinition $method): bool
    {
        $parameters = $method->parameters;

        if ($parameters->count() === 0) {
            throw new TransformerHasNoParameter($method);
        }

        if ($parameters->count() > 2) {
            throw new TransformerHasTooManyParameters($method);
        }

        if ($parameters->count() > 1 && ! $parameters->at(1)->nativeType instanceof CallableType) {
            throw new TransformerHasInvalidCallableParameter($method, $parameters->at(1)->nativeType);
        }

        return true;
    }

    private static function checkKeyTransformer(MethodDefinition $method): bool
    {
        $parameters = $method->parameters;

        if ($parameters->count() > 1) {
            throw new KeyTransformerHasTooManyParameters($method);
        }

        if ($parameters->count() > 0) {
            $type = $parameters->at(0)->type;

            if (! $type instanceof StringType) {
                throw new KeyTransformerParameterInvalidType($method);
            }
        }

        return true;
    }
}
