<?php

namespace Chiron\View\Bootloader;

use Chiron\Core\Directories;
use Chiron\Core\Container\Bootloader\AbstractBootloader;
use Chiron\Console\Console;
use Chiron\View\Command\ViewListCommand;

final class ViewCommandBootloader extends AbstractBootloader
{
    public function boot(Console $console): void
    {
        $console->addCommand(ViewListCommand::getDefaultName(), ViewListCommand::class);
    }
}
