<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\StaticAnalysis;

use CuyZ\Valinor\Mapper\TreeMapper;
use stdClass;

use function PHPStan\Testing\assertType;

function mapping_class_will_infer_object_of_same_type(TreeMapper $mapper): stdClass
{
    $result = $mapper->map(stdClass::class, []);

    /** @psalm-check-type $result = stdClass */
    assertType(stdClass::class, $result);

    return $result;
}

/**
 * @param class-string $type
 */
function mapping_class_string_will_infer_object_without_class(TreeMapper $mapper, string $type): object
{
    $result = $mapper->map($type, []);

    /** @psalm-check-type $result = object */
    assertType('object', $result);

    return $result;
}
