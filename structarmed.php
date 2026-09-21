<?php

declare(strict_types=1);

use Boundwize\StructArmed\Architecture;
use Boundwize\StructArmed\Preset\Preset;

return Architecture::define()
    // apply on qa and src directory for now
    // for ease gradual changes
    ->layer('Source', ['qa', 'src'])
    ->withPresets(Preset::PSR4());