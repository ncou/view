<?php

declare(strict_types=1);

namespace Chiron\View\Bootloader;

use Chiron\Core\Container\Bootloader\AbstractBootloader;
use Chiron\Core\Directories;

final class ViewDirectoryBootloader extends AbstractBootloader
{
    public function boot(Directories $directories): void
    {
        if (! $directories->has('@views')) {
            $directories->set('@views', $directories->get('@resources/views/'));
        }
    }
}
