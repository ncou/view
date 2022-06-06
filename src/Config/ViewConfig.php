<?php

declare(strict_types=1);

namespace Chiron\View\Config;

use Chiron\Config\AbstractInjectableConfig;
use Nette\Schema\Expect;
use Nette\Schema\Schema;

final class ViewConfig extends AbstractInjectableConfig
{
    protected const CONFIG_SECTION_NAME = 'view';

    protected function getConfigSchema(): Schema
    {
        return Expect::structure([
            'extension'    => Expect::string()->nullable(),
            'paths'        => Expect::arrayOf('string')->default([directory('@views')]),
            'dependencies' => Expect::arrayOf('string'), // TODO : améliorer le expect ca doit être un tableau de array<string, string> c'est à dire un tableau associatif de string, et plus tard faire évoluer le tableau en array<string, instanceof Reference::class> car on utilisera la classe Container\Reference::to(xxxx) pour définir dans le fichier view les dépendences dans le container !!!!
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

    public function getDependencies(): array
    {
        return $this->get('dependencies');
    }
}
