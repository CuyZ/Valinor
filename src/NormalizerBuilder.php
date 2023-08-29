<?php

declare(strict_types=1);

namespace CuyZ\Valinor;

use CuyZ\Valinor\Normalizer\Normalizer;
use CuyZ\Valinor\Library\NormalizerContainer;
use CuyZ\Valinor\Library\NormalizerSettings;

/** @api */
final class NormalizerBuilder
{
    private NormalizerSettings $settings;

    private NormalizerContainer $container;

    public function __construct()
    {
        $this->settings = new NormalizerSettings();
    }

    /**
     * @todo doc
     *
     * @param callable(object, callable(): mixed): mixed $callback
     */
    public function addHandler(callable $callback, int $priority = 0): self
    {
        $clone = clone $this;
        $clone->settings->handlers[$priority][] = $callback;

        return $clone;
    }

    public function normalizer(): Normalizer
    {
        return $this->container()->normalizer();
    }

    public function __clone()
    {
        $this->settings = clone $this->settings;
        unset($this->container);
    }

    private function container(): NormalizerContainer
    {
        return ($this->container ??= new NormalizerContainer($this->settings));
    }
}
