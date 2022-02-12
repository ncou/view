<?php

declare(strict_types=1);

namespace Chiron\View\Config;

use Nette\Schema\Expect;
use Nette\Schema\Schema;
use Chiron\Config\AbstractInjectableConfig;
use Chiron\Config\InjectableInterface;

final class ViewConfig extends AbstractInjectableConfig
{
    protected const CONFIG_SECTION_NAME = 'view';

    protected function getConfigSchema(): Schema
    {
        return Expect::structure([
            'extension' => Expect::string()->nullable(),
            'paths' => Expect::arrayOf('string')->default([directory('@views')]),
        ]);
    }

    public function getExtension(): ?string
    {
        return $this->get('extension');
    }

    public function getPaths(): array
    {
        return $this->get('paths');
    }
}
