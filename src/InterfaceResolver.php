<?php

namespace CuyZ\Valinor;

/**
 * @api
 */
interface InterfaceResolver
{
    /**
     * @param array<string,mixed>|null $props
     * @return class-string|null
     */
    public function resolve(string $interface, ?array $props): ?string;

    /**
     * @return string[]
     */
    public function getResolverProps(string $interface): array;

    /**
     * @return array<string|int,mixed>
     */
    public function transform(object $input, callable $next): array;
}
