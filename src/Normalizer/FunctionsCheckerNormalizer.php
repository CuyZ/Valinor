<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer;

use CuyZ\Valinor\Definition\FunctionsContainer;
use CuyZ\Valinor\Type\Types\CallableType;
use RuntimeException;

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
                    throw new RuntimeException('@todo'); // @todo
                }

                if ($parameters->count() > 2) {
                    throw new RuntimeException('@todo'); // @todo
                }

                if ($parameters->count() > 1 && ! $parameters->at(1)->type() instanceof CallableType) {
                    throw new RuntimeException('@todo'); // @todo
                }
            }
        }

        return $this->delegate->normalize($value);
    }
}
