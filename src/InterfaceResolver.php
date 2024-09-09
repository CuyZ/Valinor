<?php

namespace CuyZ\Valinor;

interface InterfaceResolver
{
    public function resolve(string $interface, ?array $props) : ?string;

    public function getResolverProps(string $interface) : array;

    public function transform(object $input, callable $next) : array;
}
