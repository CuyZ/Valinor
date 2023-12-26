<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer;

use CuyZ\Valinor\Definition\FunctionDefinition;
use CuyZ\Valinor\Definition\Repository\FunctionDefinitionRepository;
use CuyZ\Valinor\Normalizer\Exception\KeyTransformerHasTooManyParameters;
use CuyZ\Valinor\Normalizer\Exception\KeyTransformerParameterInvalidType;
use CuyZ\Valinor\Type\StringType;

/** @internal */
final class KeyTransformersHandler
{
    /** @var array<string, true> */
    private array $keyTransformerCheck = [];

    public function __construct(
        private FunctionDefinitionRepository $functionDefinitionRepository,
    ) {}

    /**
     * @param list<object> $attributes
     */
    public function transformKey(string|int $key, array $attributes): string|int
    {
        foreach ($attributes as $attribute) {
            if (! method_exists($attribute, 'normalizeKey')) {
                continue;
            }

            // PHP8.1 First-class callable syntax
            $function = $this->functionDefinitionRepository->for([$attribute, 'normalizeKey']);

            $this->checkKeyTransformer($function);

            if ($function->parameters()->count() === 0 || $function->parameters()->at(0)->type()->accepts($key)) {
                $key = $attribute->normalizeKey($key);
            }
        }

        return $key;
    }

    private function checkKeyTransformer(FunctionDefinition $function): void
    {
        if (isset($this->keyTransformerCheck[$function->signature()])) {
            return;
        }

        // @infection-ignore-all
        $this->keyTransformerCheck[$function->signature()] = true;

        $parameters = $function->parameters();

        if ($parameters->count() > 1) {
            throw new KeyTransformerHasTooManyParameters($function);
        }

        if ($parameters->count() > 0) {
            $type = $parameters->at(0)->type();

            if (! $type instanceof StringType) {
                throw new KeyTransformerParameterInvalidType($function);
            }
        }
    }
}
