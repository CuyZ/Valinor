<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer;

use CuyZ\Valinor\Definition\FunctionsContainer;
use CuyZ\Valinor\Normalizer\Exception\TransformerHasInvalidCallableParameter;
use CuyZ\Valinor\Normalizer\Exception\TransformerHasNoParameter;
use CuyZ\Valinor\Normalizer\Exception\TransformerHasTooManyParameters;
use CuyZ\Valinor\Type\Types\CallableType;

/** @internal */
final class FunctionsCheckerNormalizer implements Normalizer
{
    private bool $checkWasDone = false;

    public function __construct(
        private Normalizer $delegate,
        private FunctionsContainer $handlers
    ) {}

    public function normalize(mixed $value): mixed
    {
        if (! $this->checkWasDone) {
            $this->checkWasDone = true;

            foreach ($this->handlers as $function) {
                $parameters = $function->definition()->parameters();

                if ($parameters->count() === 0) {
                    throw new TransformerHasNoParameter($function->definition());
                }

                if ($parameters->count() > 2) {
                    throw new TransformerHasTooManyParameters($function->definition());
                }

                if ($parameters->count() > 1 && ! $parameters->at(1)->type() instanceof CallableType) {
                    throw new TransformerHasInvalidCallableParameter($function->definition(), $parameters->at(1)->type());
                }
            }
        }

        return $this->delegate->normalize($value);
    }
}
