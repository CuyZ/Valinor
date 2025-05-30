<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Library;

use CuyZ\Valinor\Library\Settings;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;
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

        self::assertSame('2a24fb52f61377b93f562e764ed4dbec', $settings->hash());
    }

    public function test_settings_hash(): void
    {
        $settings = new Settings();
        $settings->inferredMapping[stdClass::class] = fn () => stdClass::class;
        $settings->nativeConstructors[stdClass::class] = null;
        $settings->customConstructors[] = fn (): stdClass => new stdClass();
        $settings->valueModifier[] = fn (string $value): string => $value;
        $settings->cache = $this->createMock(CacheInterface::class);
        $settings->supportedDateFormats = ['Y-m-d\\TH:i:sP'];
        $settings->allowScalarValueCasting = true;
        $settings->allowNonSequentialList = true;
        $settings->allowUndefinedValues = true;
        $settings->allowSuperfluousKeys = true;
        $settings->allowPermissiveTypes = true;
        $settings->exceptionFilter = fn (Throwable $e) => throw $e;
        $settings->transformers[] = [fn (mixed $value): mixed => $value];
        $settings->transformerAttributes[stdClass::class] = null;

        self::assertSame('0beb5be3f63aae0ba6e8c9b0f1040301', $settings->hash());
    }
}
