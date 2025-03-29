<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Normalizer;

use Attribute;
use CuyZ\Valinor\Cache\FileSystemCache;
use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Normalizer\Format;
use DateTime;
use DateTimeInterface;
use IteratorAggregate;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;
use Traversable;

use function glob;

final class NormalizerCompiledCodeTest extends TestCase
{
    /**
     * This test is here to help detect regressions in the generated PHP code.
     * Even if the generated code works, it does not mean that there are no
     * performance regressions or other issues. For instance, the generated code
     * could always call the "joker" `transform_mixed` method, which will always
     * return a correct result but is suboptimal.
     *
     * This test helps to detect such issues by comparing the generated code
     * with a file that contains the expected code.
     *
     * @param list<callable> $transformers
     */
    #[DataProvider('compiled_code_is_correct_data_provider')]
    public function test_compiled_code_is_correct(mixed $input, string $expectedFile, array $transformers = []): void
    {
        $directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . bin2hex(random_bytes(16));

        $cache = new FileSystemCache($directory);

        $builder = (new MapperBuilder())->withCache($cache);
        $builder = $builder->registerTransformer(PrependToStringAttribute::class);

        foreach ($transformers as $transformer) {
            $builder = $builder->registerTransformer($transformer);
        }

        $builder->normalizer(Format::array())->normalize($input);

        $cacheFiles = glob($directory . DIRECTORY_SEPARATOR . 'transformer-*.php');

        if ($cacheFiles === false) {
            self::fail('Failed to find any cache file');
        }

        // To help updating the expectation file, uncomment the following line
        // and run the test once. Then, comment it back. Remember to check that
        // the updated content is correct.
        // file_put_contents($expectedFile, file_get_contents($cacheFiles[0]));

        self::assertFileEquals($expectedFile, $cacheFiles[0]);

        $cache->clear();
    }

    public static function compiled_code_is_correct_data_provider(): iterable
    {
        yield 'iterable of scalars without transformers' => [
            'input' => ['some string', 42, 1337.404, true],
            'expectedFile' => __DIR__ . '/ExpectedCache/iterable-of-scalars-without-transformers.php',
        ];

        yield 'iterable of scalars with transformers' => [
            'input' => ['some string', 42, 1337.404, true],
            'expectedFile' => __DIR__ . '/ExpectedCache/iterable-of-scalars-with-transformers.php',
            'transformers' => [
                static fn (string $value) => $value . '!',
                static fn (int $value) => $value + 1,
                static fn (float $value) => $value + 0.1,
                static fn (bool $value) => ! $value,
            ],
        ];

        yield 'iterable of objects' => [
            'input' => [new stdClass(), new stdClass()],
            'expectedFile' => __DIR__ . '/ExpectedCache/iterable-of-objects.php',
        ];

        yield 'class with two properties' => [
            'input' => new ClassWithTwoProperties('foo', 42),
            'expectedFile' => __DIR__ . '/ExpectedCache/class-with-two-properties.php',
        ];

        yield 'class with transformers on first property' => [
            'input' => new ClassWithTwoProperties('foo', 42),
            'expectedFile' => __DIR__ . '/ExpectedCache/class-with-transformers-on-first-property.php',
            'transformers' => [
                static fn (string $value) => $value . '!',
            ],
        ];

        yield 'class with `DateTimeInterface`' => [
            'input' => new ClassWithDateTimeInterface(new DateTime()),
            'expectedFile' => __DIR__ . '/ExpectedCache/class-with-datetime-interface.php',
            'transformers' => [
                static fn (DateTimeInterface $value) => $value->getTimestamp(),
            ],
        ];

        yield 'class with unsealed shaped array' => [
            'input' => new ClassWithUnsealedShapedArray([
                'someString' => 'foo',
                'someInt' => 42,
            ]),
            'expectedFile' => __DIR__ . '/ExpectedCache/class-with-unsealed-shaped-array.php',
        ];

        yield 'class with union' => [
            'intput' => new ClassWithUnion('foo'),
            'expectedFile' => __DIR__ . '/ExpectedCache/class-with-union.php',
        ];

        yield 'generator with scalars' => [
            'input' => (function () {
                yield 'foo';
                yield 42;
            })(),
            'expectedFile' => __DIR__ . '/ExpectedCache/generator-with-scalars.php',
        ];

        yield 'class implementing `IteratorAggregate`' => [
            'input' => new ClassImplementingIteratorAggregate(),
            'expectedFile' => __DIR__ . '/ExpectedCache/class-implementing-iterator-aggregate.php',
        ];

        yield 'class with unresolvable type and string native type' => [
            'input' => new ClassWithUnresolvableTypeAndStringNativeType('foo'),
            'expectedFile' => __DIR__ . '/ExpectedCache/class-with-unresolvable-type-and-string-native-type.php',
        ];

        yield 'class with unresolvable type and mixed native type' => [
            'input' => new ClassWithUnresolvableTypeAndMixedNativeType('foo'),
            'expectedFile' => __DIR__ . '/ExpectedCache/class-with-unresolvable-type-and-mixed-native-type.php',
        ];
    }
}

final class ClassWithTwoProperties
{
    public function __construct(
        public string $valueA,
        public int $valueB,
    ) {}
}

final class ClassWithDateTimeInterface
{
    public function __construct(
        public DateTimeInterface $date,
    ) {}
}

final class ClassWithUnion
{
    public function __construct(
        public string|DateTimeInterface $value,
    ) {}
}

final class ClassWithUnsealedShapedArray
{
    // @phpstan-ignore missingType.iterableValue (PHPStan does not (yet) understand the unsealed shaped array syntax)
    public function __construct(
        /** @var array{someString: string, someInt: int, ...array<int, string>} */
        public array $shapedArray,
    ) {}
}

/**
 * @implements IteratorAggregate<string|int>
 */
final class ClassImplementingIteratorAggregate implements IteratorAggregate
{
    public function getIterator(): Traversable
    {
        yield 'foo';
        yield 42;
    }
}

#[Attribute(Attribute::TARGET_PROPERTY)]
final class PrependToStringAttribute
{
    public function normalize(string $value, callable $next): string
    {
        // @phpstan-ignore binaryOp.invalid (we cannot set closure parameters / see https://github.com/phpstan/phpstan/issues/3770)
        return $next() . '!';
    }
}

final class ClassWithUnresolvableTypeAndStringNativeType
{
    public function __construct(
        #[PrependToStringAttribute]
        /** @var unresolvable */
        public string $value,
    ) {}
}

final class ClassWithUnresolvableTypeAndMixedNativeType
{
    public function __construct(
        #[PrependToStringAttribute]
        /** @var unresolvable */
        public mixed $value,
    ) {}
}
