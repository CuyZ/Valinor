<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Functional\Cache;

use Attribute;
use CuyZ\Valinor\Mapper\AsConverter;

#[Attribute, AsConverter]
final class SomeAttributeForClassToTestTypeFilesWatcher {}
