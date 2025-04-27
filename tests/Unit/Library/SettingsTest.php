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

        self::assertSame('8f80a356e617c1920fc611ec22ba3709', $settings->hash());
    }

    public function test_settings_hash(): void
    {
        $settings = new Settings();
        $settings->inferredMapping[stdClass::class] = fn () => stdClass::class;
        $settings->nativeConstructors[stdClass::class] = null;
        $settings->customConstructors[] = fn (): stdClass => new stdClass();
        $settings->valueModifier[] = fn (string $value): string => $value;
        $settings->cache = new FakeCache();
        $settings->supportedDateFormats = ['Y-m-d\\TH:i:sP'];
        $settings->allowScalarValueCasting = true;
        $settings->allowNonSequentialList = true;
        $settings->allowUndefinedValues = true;
        $settings->allowSuperfluousKeys = true;
        $settings->allowPermissiveTypes = true;
        $settings->exceptionFilter = fn (Throwable $e) => throw $e;
        $settings->transformers[] = [fn (mixed $value): mixed => $value];
        $settings->transformerAttributes[stdClass::class] = null;

        self::assertSame('077b2176cc9543073e21608f575e2146', $settings->hash());
    }
}
