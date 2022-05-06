<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Utility;

use Composer\InstalledVersions;

/** @internal */
final class Package
{
    private static string $version;

    public static function version(): string
    {
        /** @infection-ignore-all */
        return self::$version ??= InstalledVersions::getVersion('cuyz/valinor') ?? 'unknown';
    }
}
