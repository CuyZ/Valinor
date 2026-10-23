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

        self::assertSame('e8506487186797525dedf7fce5d8f157', $settings->hash());
    }

    public function test_settings_hash(): void
    {
        $settings = new Settings();
        $settings->inferredMapping[stdClass::class] = fn () => stdClass::class;
        $settings->nativeConstructors[stdClass::class] = null;
        $settings->customConstructors[] = fn (): stdClass => new stdClass();
        $settings->cache = new FakeCache();
        $settings->supportedDateFormats = ['Y-m-d\\TH:i:sP'];
        $settings->allowCastingToBoolean = ['true' => ['1', 'true'], 'false' => ['0', 'false']];
        $settings->allowCastingToInteger = true;
        $settings->allowCastingToFloat = true;
        $settings->allowCastingToString = true;
        $settings->allowUndefinedValues = true;
        $settings->allowSuperfluousKeys = true;
        $settings->allowPermissiveTypes = true;
        $settings->mapperConverters[] = [fn (mixed $value): mixed => $value];
        $settings->keyConverters[] = fn (string $key): string => $key;
        $settings->mapperConverterAttributes[stdClass::class] = null;
        $settings->exceptionFilter = fn (Throwable $e) => throw $e;
        $settings->normalizerTransformers[] = [fn (mixed $value): mixed => $value];
        $settings->normalizerTransformerAttributes[stdClass::class] = null;

        self::assertSame('8b67f7f64ae036f57572bdcbe284fb7a', $settings->hash());
    }
}
