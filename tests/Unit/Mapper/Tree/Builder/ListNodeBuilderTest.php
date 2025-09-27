<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Mapper\Tree\Builder;

use CuyZ\Valinor\Library\Settings;
use CuyZ\Valinor\Mapper\Tree\Builder\ListNodeBuilder;
use CuyZ\Valinor\Mapper\Tree\Builder\RootNodeBuilder;
use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Tests\Functional\FunctionalTestCase;
use CuyZ\Valinor\Type\Dumper\TypeDumper;
use CuyZ\Valinor\Type\Types\ListType;

final class ListNodeBuilderTest extends FunctionalTestCase
{
    public function test_build_with_null_value_and_undefined_values_allowed_returns_empty_branch_node(): void
    {
        $setting = new Settings();
        $setting->allowUndefinedValues = true;

        $shell = Shell::root($setting, $this->getService(TypeDumper::class), ListType::native(), null);

        $node = (new RootNodeBuilder(new ListNodeBuilder()))->build($shell);

        self::assertTrue($node->isValid());
        self::assertSame([], $node->value());
    }
}
