<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Utility;

use PackageVersions\Versions;

final class Package
{
    private static string $version;

    public static function version(): string
    {
        /** @infection-ignore-all */
        return self::$version ??= Versions::getVersion('cuyz/valinor');
    }
}
