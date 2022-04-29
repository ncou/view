<?php

declare(strict_types=1);

namespace Chiron\View\Native;

use Chiron\View\ViewInterface;
use Chiron\View\EngineInterface;
use Chiron\View\ViewLoader;
use Chiron\View\ViewContext;

final class NativeEngine implements EngineInterface
{
    protected const EXTENSION = 'php'; //'phtml'

    /** @var ViewLoader */
    protected ViewLoader $loader;

    /**
     * {@inheritdoc}
     */
    public function withLoader(ViewLoader $loader): EngineInterface
    {
        $engine = clone $this;
        //$engine->loader = $loader->withExtension($this->extension ?? static::EXTENSION); // TODO : code à utiliser
        $engine->loader = $loader->withExtension(static::EXTENSION); // TODO : code à utiliser

        return $engine;
    }

    /**
     * {@inheritdoc}
     */
    public function getLoader(): ViewLoader
    {
        if (empty($this->loader)) {
            throw new EngineException('No associated loader found');
        }

        return $this->loader;
    }

    // TODO : passer le viewcontext ???
    public function getView(string $path, array $parameters = []): ViewInterface
    {
        // TODO : passer le viewcontext ???
        return new NativeView($this->getLoader()->load($path), $parameters);
    }

}
