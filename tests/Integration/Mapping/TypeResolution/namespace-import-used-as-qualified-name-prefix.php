<?php

namespace NamespaceImportUsedAsQualifiedNamePrefix\SomeNamespace\SubNamespace {
    interface SomeInterface {}

    final class SomeClass implements SomeInterface {}
}

namespace NamespaceImportUsedAsQualifiedNamePrefix\OtherNamespace {
    use NamespaceImportUsedAsQualifiedNamePrefix\SomeNamespace;

    final readonly class SomeObject
    {
        /**
         * @param class-string<SomeNamespace\SubNamespace\SomeInterface> $className
         */
        public function __construct(
            public string $className,
        ) {}
    }
}
