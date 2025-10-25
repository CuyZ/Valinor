<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\StaticAnalysis\PHPUnit;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Source\Source;
use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\QA\PHPUnit\CollectValinorMappingErrors;
use PHPUnit\Framework\TestCase;

final class MappingTest extends TestCase
{
    use CollectValinorMappingErrors;

    public function testMapping(): void
    {
        $result = (new MapperBuilder())
            ->mapper()
            ->map(
                SimpleMappingSubject::class,
                Source::array([
                    'foo' => 'bar',
                    'bar' => false,
                ])
            );

        self::assertEquals('bar', $result->foo);
    }

    public function testMappingWhichThrowsError(): void
    {
        $this->expectException(MappingError::class);
        (new MapperBuilder())
            ->mapper()
            ->map(
                SimpleMappingSubject::class,
                Source::array([
                    'foo' => null,
                    'bar' => null,
                ])
            );
    }
}