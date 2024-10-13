<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer;

use CuyZ\Valinor\Definition\AttributeDefinition;
use CuyZ\Valinor\Definition\FunctionDefinition;
use CuyZ\Valinor\Definition\MethodDefinition;
use CuyZ\Valinor\Definition\Repository\FunctionDefinitionRepository;
use CuyZ\Valinor\Normalizer\AsTransformer;
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

    /** @var array<string, true> */
    private array $transformerCheck = [];

    public function __construct(
        private FunctionDefinitionRepository $functionDefinitionRepository,
        /** @var list<callable> */
        private array $transformers,
        /** @var list<class-string> */
        private array $transformerAttributes,
    ) {}

    public function hasTransformers(): bool
    {
        return $this->transformers !== [];
    }

    public function hasTransformerAttributes(): bool
    {
        return $this->transformerAttributes !== [];
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

                $this->checkTransformer($function);
            }
        }

        return $this->transformers;
    }

    public function filterTransformerAttributes(AttributeDefinition $attribute): bool
    {
        return $this->isAttributeRegistered($attribute)
            && $attribute->class->methods->has('normalize')
            && $this->checkTransformer($attribute->class->methods->get('normalize'));
    }

    public function filterKeyTransformerAttributes(AttributeDefinition $attribute): bool
    {
        return $this->isAttributeRegistered($attribute)
            && $attribute->class->methods->has('normalizeKey')
            && $this->checkKeyTransformer($attribute->class->methods->get('normalizeKey'));
    }

    private function isAttributeRegistered(AttributeDefinition $attribute): bool
    {
        if ($attribute->class->attributes->has(AsTransformer::class)) {
            return true;
        }

        foreach ($this->transformerAttributes as $transformerAttribute) {
            if (is_a($attribute->class->type->className(), $transformerAttribute, true)) {
                return true;
            }
        }

        return false;
    }

    private function checkTransformer(MethodDefinition|FunctionDefinition $method): bool
    {
        if (isset($this->transformerCheck[$method->signature])) {
            return true;
        }

        // @infection-ignore-all
        $this->transformerCheck[$method->signature] = true;

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

    private function checkKeyTransformer(MethodDefinition $method): bool
    {
        if (isset($this->transformerCheck[$method->signature])) {
            return true;
        }

        // @infection-ignore-all
        $this->transformerCheck[$method->signature] = true;

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
