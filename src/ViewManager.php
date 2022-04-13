<?php

declare(strict_types=1);

namespace Chiron\View;

use Chiron\View\Exception\ViewException;
use Chiron\View\Native\NativeEngine;
use Chiron\View\EngineInterface;
use Chiron\View\Config\ViewConfig;
use Chiron\Container\SingletonInterface;

//https://github.com/spiral/views/blob/5d2123adc3cca2dc3e3c4ca0b9fe77d5ab2bf660/src/ViewManager.php

// TODO : utiliser plutot un pluriel pour les vues ? cad utiliser un nom de classe : ViewsManager
// TODO : passer la classe en final ????
class ViewManager implements SingletonInterface
{
    /** @var ViewLoader */
    private ViewLoader $loader;

    /** @var EngineInterface[] */
    private array $engines = [];

    // TODO : ajouter une méthode pour ajouter un namespace+path associé
    public function __construct(ViewConfig $config)
    {

/*
        // add template paths
        foreach ($config->getPaths() as $namespace => $paths) {
            // TODO : créer une constante EMPTY_NAMESPACE dans la classe TemplateRenderInterface ??? ca serai plus propre que d'utiliser directement "null" dans le code ci dessous !!!
            $namespace = is_int($namespace) ? null : $namespace;

            foreach ((array) $paths as $path) {
                $renderer->addPath($path, $namespace);
            }
        }
*/

        $namespaces = ['default' => [directory('@views')]]; // TODO : aller chercher ces informations dans le fichier de config !!!
        $namespaces = [directory('@views')]; // TODO : aller chercher ces informations dans le fichier de config !!!

        $this->loader = new ViewLoader($namespaces);

        // TODO : à virer c'est un test.
        $this->addEngine(new NativeEngine());
    }

    /**
     * Attach new view engine.
     */
    public function addEngine(EngineInterface $engine): void
    {
        $this->engines[] = $engine->withLoader($this->loader);

        // TODO : utilité du sort() sur les extensions ????
        uasort($this->engines, static function (EngineInterface $a, EngineInterface $b) {
            return strcmp($a->getLoader()->getExtension(), $b->getLoader()->getExtension());
        });

        $this->engines = array_values($this->engines);
    }

    /**
     * Get all associated view engines.
     *
     * @return EngineInterface[]
     */
    public function getEngines(): array
    {
        return $this->engines;
    }




    /**
     * @throws ViewException
     */
    public function render(string $path, array $data = []): string
    {
        return $this->get($path)->render($data);
    }

    /**
     * Get view from one of the associated engines.
     *
     * @throws ViewException
     */
    // TODO : il faudrait initialiser le assign('title') ici et ensuite retourner la view !!!!
    public function get(string $path): ViewInterface
    {
        // TODO : utilser un cache ????
        // TODO : lui passer le context !!!!
        //return $this->findEngine($path)->get($path, $this->context);
        return $this->findEngine($path)->get($path);
    }

    /**
     * @throws ViewException
     */
    private function findEngine(string $path): EngineInterface
    {
        foreach ($this->engines as $engine) {
            if ($engine->getLoader()->exists($path)) {
                return $engine;
            }
        }

        throw new ViewException("Unable to detect view engine for `{$path}`."); // TODO : utiliser un sprintf
    }

}
