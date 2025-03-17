<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Normalizer;

use CuyZ\Valinor\Cache\FileSystemCache;
use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Normalizer\Format;
use DateTime;
use DateTimeInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function glob;

final class NormalizerCompiledCodeTest extends TestCase
{
    /**
     * This test is here to help detect regression in the generated PHP code.
     * Even if the generated code works, some performance/other things may not
     * be as expected. This test helps to detect such issues by comparing the
     * generated code with a file that contains the expected code.
     *
     * @param list<callable> $transformers
     */
    #[DataProvider('compiled_code_is_correct_data_provider')]
    public function test_compiled_code_is_correct(mixed $input, string $expectedFile, array $transformers = []): void
    {
        $directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . bin2hex(random_bytes(16));

        $cache = new FileSystemCache($directory);

        $builder = (new MapperBuilder())->withCache($cache);

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

        yield 'class with `DateTimeInterface`' => [
            'input' => new ClassWithDateTimeInterface(new DateTime()),
            'expectedFile' => __DIR__ . '/ExpectedCache/class-with-datetime-interface.php',
        ];
    }
}

final class ClassWithDateTimeInterface
{
    public function __construct(
        public DateTimeInterface $date,
    ) {}
}
