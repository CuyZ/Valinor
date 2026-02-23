<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Library;

use CuyZ\Valinor\Library\Settings;
use CuyZ\Valinor\Tests\Fake\Cache\FakeCache;
use CuyZ\Valinor\Tests\Unit\UnitTestCase;
use stdClass;
use Throwable;

final class SettingsTest extends UnitTestCase
{
    /**
     * This test is here to detect if the default settings have changed,
     * especially when a property has been added. It this happens, the tests
     * below should be adapted to adapt accordingly.
     */
    public function test_default_settings_hash(): void
    {
        $settings = new Settings();

        self::assertSame('9d463a6dbb306e97747b5f2c5930b639', $settings->hash());
    }

    public function test_settings_hash(): void
    {
        $settings = new Settings();
        $settings->inferredMapping[stdClass::class] = fn () => stdClass::class;
        $settings->nativeConstructors[stdClass::class] = null;
        $settings->customConstructors[] = fn (): stdClass => new stdClass();
        $settings->cache = new FakeCache();
        $settings->supportedDateFormats = ['Y-m-d\\TH:i:sP'];
        $settings->allowScalarValueCasting = true;
        $settings->allowNonSequentialList = true;
        $settings->allowUndefinedValues = true;
        $settings->allowSuperfluousKeys = true;
        $settings->allowPermissiveTypes = true;
        $settings->mapperConverters[] = [fn (mixed $value): mixed => $value];
        $settings->keyConverters[] = fn (string $key): string => $key;
        $settings->mapperConverterAttributes[stdClass::class] = null;
        $settings->exceptionFilter = fn (Throwable $e) => throw $e;
        $settings->normalizerTransformers[] = [fn (mixed $value): mixed => $value];
        $settings->normalizerTransformerAttributes[stdClass::class] = null;

        self::assertSame('82a94b1cb67095c55c5720d125de90b1', $settings->hash());
    }
}
