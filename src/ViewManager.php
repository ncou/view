<?php

declare(strict_types=1);

namespace Chiron\View;

use Chiron\Container\Container;
use Chiron\Container\SingletonInterface;
use Chiron\View\Config\ViewConfig;
use Chiron\View\Exception\ViewException;
use Chiron\View\Helper\HtmlHelper;
use Chiron\View\Helper\UrlHelper;
use Chiron\View\Native\NativeEngine;

// TODO : utiliser le nom du controller pour préfixer le répertoire des vues ????
//https://github.com/yiisoft/yii-view/blob/406384e8a54c98e6aeb519356cb2108dff646134/src/ViewRenderer.php#L531
// TODO : auto render si il n'y a pas de réponse retournée, je pense que nous on devrait faire un event lors du call du handler pour transformer le retour de null en une response !!!!
//https://github.com/cakephp/cakephp/blob/32e3c532fea8abe2db8b697f07dfddf4dfc134ca/src/Controller/Controller.php#L545


//https://github.com/spiral/views/blob/5d2123adc3cca2dc3e3c4ca0b9fe77d5ab2bf660/src/ViewManager.php

// TODO : utiliser plutot un pluriel pour les vues ? cad utiliser un nom de classe : ViewsManager
// TODO : passer la classe en final ????
class ViewManager implements SingletonInterface
{
    /** @var ViewLoader */
    private ViewLoader $loader;
    /** @var EngineInterface[] */
    private array $engines = [];
    /** @var array<string, object> */
    private array $dependencies = []; // TODO : attention il est possible que dans le fichier de config on ait autre chose qu'un "object" par exemple "$dependencies['csrf' => true]" et ca doit pouvoir fonctionner !!!! modifier la phpdoc pour cette variable.
    private array $helpers = [
        'Url'  => UrlHelper::class,
        'Html' => HtmlHelper::class,
    ];

    // TODO : ajouter une méthode pour ajouter un namespace+path associé
    public function __construct(ViewConfig $config, Container $container)
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

        // TODO : prévoir une méthode setDependencies() ??? ou addDependency($name, $object) ????
        // add view dependencies
        foreach ($config->getDependencies() as $name => $dependency) {
            $this->dependencies[$name] = $container->get($dependency);
            // TODO : renplacer ce bout de code par Reference::to(XXXX) dans le fichier de config et on fait un $dependency->resolve($container) directement dans la classe de Config quand on appel le getDependencies, sinon dans cette boucle ici.
            // TODO : attention il est possible que dans le fichier de config on ait autre chose qu'un object par exemple "$dependencies['csrf' => true]" et ca doit pouvoir fonctionner !!!!
        }

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
        uasort($this->engines, static fn (EngineInterface $a, EngineInterface $b) => strcmp($a->getLoader()->getExtension(), $b->getLoader()->getExtension()));

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
    // TODO : utilité de cette fonction ???
    public function render(string $template, array $parameters = []): string
    {
        // TODO : prévoir de lever des events BeforeRender et AfterRender ????
        //https://github.com/cakephp/cakephp/blob/101b179920a7b8ab68866554539d65c64c6bfd8d/src/View/View.php#L768
        //https://github.com/yiisoft/view/blob/c81f3b910528dcefa3f02ef8a118da4fe16df218/src/ViewTrait.php#L440
        return $this->get($template, $parameters)->render();
    }

    /**
     * Get view from one of the associated engines.
     *
     * @throws ViewException
     */
    // TODO : renommer en getView() ???
    public function get(string $template): ViewInterface
    {
        // TODO : utiliser un cache ????
        // TODO : lui passer le context !!!!
        //return $this->findEngine($path)->get($path, $this->context);
        //return $this->findEngine($template)->getView($template, $this->dependencies);

        $view = $this->findEngine($template)->getView($template, $this->dependencies);
        // Assign by default a title for the template.
        $view->assign('title', $template); //TODO : faire un Str::Humanize() ou Str::title() sur le titre. ou alors utiliser une classe inflector !!!!
        //https://github.com/cakephp/cakephp/blob/5.x/src/View/View.php#L829
        //https://github.com/cakephp/cakephp/blob/876a11e172b0b33710b1fbddd94de6d1618d352b/src/Utility/Inflector.php#L427

        foreach ($this->helpers as $alias => $class) {
            $view->helper($alias, $class);
        }

        return $view;
    }

    public function addHelper(string $alias, string $class): void
    {
        $this->helpers[$alias] = $class;
    }

    /**
     * @throws ViewException
     */
    private function findEngine(string $template): EngineInterface
    {
        foreach ($this->engines as $engine) {
            if ($engine->getLoader()->exists($template)) {
                return $engine;
            }
        }

        throw new ViewException("Unable to detect view engine for `{$template}`."); // TODO : utiliser un sprintf
    }
}
