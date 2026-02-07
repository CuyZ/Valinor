<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Mapper;

use CuyZ\Valinor\Mapper\Exception\InvalidMappingTypeSignature;
use CuyZ\Valinor\Mapper\TreeMapper;
use CuyZ\Valinor\Tests\Unit\UnitTestCase;

final class TreeMapperTest extends UnitTestCase
{
    public function test_invalid_mapping_type_signature_throws_exception(): void
    {
        $this->expectException(InvalidMappingTypeSignature::class);
        $this->expectExceptionMessage('Could not parse the type `foo` that should be mapped: cannot parse unknown symbol `foo`.');

        $this->getService(TreeMapper::class)->map('foo', []);
    }
}
