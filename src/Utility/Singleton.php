<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Utility;

use CuyZ\Valinor\Utility\Reflection\PhpParser;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\Reader;

/** @internal */
final class Singleton
{
    private static Reader $annotationReader;

    private static PhpParser $phpParser;

    public static function annotationReader(): Reader
    {
        if (! isset(self::$annotationReader)) {
            self::$annotationReader = new AnnotationReader();

            /** @infection-ignore-all */
            AnnotationRegistry::registerUniqueLoader('class_exists');
        }

        return self::$annotationReader;
    }

    public static function phpParser(): PhpParser
    {
        return self::$phpParser ??= new PhpParser();
    }
}
