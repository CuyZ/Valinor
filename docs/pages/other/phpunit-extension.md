# PHPUnit extension

To help debug mapping errors thrown during your test suite, an extension for
[PHPUnit] is provided. It will collect any uncaught instances of `MappingError`
and display them per test in a formatted table at the end of the test run.

If installed correctly and a mapping error is thrown, PHPUnit will show a table
after execution for each test case with errors:

```
The following Valinor mapping errors were thrown:

+---------- CuyZ\Valinor\QA\StaticAnalysis\phpunit\MappingTest -----------+
| Method                      | Path | Error                              |
+-----------------------------+------+------------------------------------+
| testMappingWhichThrowsError | foo  | Value null is not a valid string.  |
| testMappingWhichThrowsError | bar  | Value null is not a valid boolean. |
+-----------------------------+------+------------------------------------+
```

**Activating**

To activate this feature, the extension must be registered with PHPUnit:

```xml title="phpunit.dist.xml"
<extensions>
    <bootstrap class="CuyZ\Valinor\QA\PHPUnit\PrettyPrintMappingErrorsExtension"/>
</extensions>
```

Additionally, every test case where `MappingError` may be thrown also needs to 
have a trait added to it:

```php title="MyTestCase.php"
<?php

use CuyZ\Valinor\QA\PHPUnit\CollectValinorMappingErrors;
use PHPUnit\Framework\TestCase;

final class MappingTest extends TestCase
{
    use CollectValinorMappingErrors;
    
    // ...
}
```

This trait overrides the `TestCase::transformException` method, so if you have
overridden this method already you will have to call the trait method yourself.

```php title="MyTestCase.php"
<?php

use CuyZ\Valinor\QA\PHPUnit\CollectValinorMappingErrors;
use PHPUnit\Framework\TestCase;
use Throwable;

final class MappingTest extends TestCase
{
    use CollectValinorMappingErrors { transformException as protected collectValinorMappingErrors; }

    protected function transformException(Throwable $t): Throwable
    {
        // ...

        return $this->collectValinorMappingErrors($t);
    }
```
