<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Namespace;

use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;
use SimpleNamespace\ImplementationOne;

final class NamespacedInterfaceInferringTest extends IntegrationTestCase
{
    // @see https://github.com/CuyZ/Valinor/issues/394#issuecomment-1746722996
    public function test_interface_inferred_from_same_namespace_as_file_runs_correctly(): void
    {
        // This file is excluded from the test so that it doesn't get autoloaded
        // by the test suite; this allows testing a specific scenario where the
        // mapper infers an interface from the same namespace as the file it is
        // called from.
        $result = require_once 'mapper-inferring-interface-from-non-class.php';

        self::assertInstanceOf(ImplementationOne::class, $result);
    }
}
