<?php

namespace CuyZ\Valinor\Tests\StaticAnalysis;

use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Normalizer\Format;
use CuyZ\Valinor\Normalizer\Normalizer;

use function PHPStan\Testing\assertType;

function normalizer_with_array_format_is_inferred_properly(): void
{
    $result = (new MapperBuilder())->normalizer(Format::array())->normalize(['foo' => 'bar']);

    /** @psalm-check-type $result = array|bool|float|int|string|null */
    assertType('array|bool|float|int|string|null', $result);
}

function normalize_with_covariant_template_is_inferred_properly(): void
{
    $normalizer = (new MapperBuilder())->normalizer(Format::array());

    normalizeWithMixedType($normalizer);
}

/**
 * @param Normalizer<mixed> $normalizer
 */
function normalizeWithMixedType(Normalizer $normalizer): mixed
{
    return $normalizer->normalize('foo');
}
