<?php

declare(strict_types=1);

namespace Chiron\View\Bootloader;

use Chiron\Console\Console;
use Chiron\Core\Container\Bootloader\AbstractBootloader;
use Chiron\View\Command\ViewListCommand;

final class ViewCommandBootloader extends AbstractBootloader
{
    public function boot(Console $console): void
    {
        $console->addCommand(ViewListCommand::getDefaultName(), ViewListCommand::class);
    }
}
