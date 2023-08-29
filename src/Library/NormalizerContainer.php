<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Library;

use CuyZ\Valinor\Definition\FunctionsContainer;
use CuyZ\Valinor\Definition\Repository\FunctionDefinitionRepository;
use CuyZ\Valinor\Normalizer\FunctionsCheckerNormalizer;
use CuyZ\Valinor\Normalizer\Normalizer;
use CuyZ\Valinor\Normalizer\RecursiveNormalizer;

/** @internal */
final class NormalizerContainer
{
    private SharedContainer $shared;

    /** @var array<class-string, object> */
    private array $services = [];

    /** @var array<class-string, callable(): object> */
    private array $factories;

    public function __construct(NormalizerSettings $settings)
    {
        $this->shared = SharedContainer::new($settings->cache);
        $this->factories = [
            Normalizer::class => function () use ($settings) {
                $functions = new FunctionsContainer(
                    $this->shared->get(FunctionDefinitionRepository::class),
                    $settings->sortedHandlers()
                );

                $normalizer = new RecursiveNormalizer($functions);

                return new FunctionsCheckerNormalizer($normalizer, $functions);
            },
        ];
    }

    public function normalizer(): Normalizer
    {
        return $this->get(Normalizer::class);
    }

    /**
     * @template T of object
     * @param class-string<T> $name
     * @return T
     */
    private function get(string $name): object
    {
        return $this->services[$name] ??= call_user_func($this->factories[$name]); // @phpstan-ignore-line
    }
}
