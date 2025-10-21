<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Library;

use CuyZ\Valinor\Library\Settings;
use CuyZ\Valinor\Tests\Fake\Cache\FakeCache;
use PHPUnit\Framework\TestCase;
use stdClass;
use Throwable;

final class SettingsTest extends TestCase
{
    /**
     * This test is here to detect if the default settings have changed,
     * especially when a property has been added. It this happens, the tests
     * below should be adapted to adapt accordingly.
     */
    public function test_default_settings_hash(): void
    {
        $settings = new Settings();

        self::assertSame('8f8533f4d1ebb7166fd29213e5f0a91c', $settings->hash());
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
        $settings->mapperConverterAttributes[stdClass::class] = null;
        $settings->exceptionFilter = fn (Throwable $e) => throw $e;
        $settings->normalizerTransformers[] = [fn (mixed $value): mixed => $value];
        $settings->normalizerTransformerAttributes[stdClass::class] = null;

        self::assertSame('0de112e7f484a9c56d5fbdaca7f04a20', $settings->hash());
    }
}
