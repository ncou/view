<?php

declare(strict_types=1);

namespace Chiron\View\Engine;

use Chiron\View\AttributesTrait;
use Chiron\View\ExtensionTrait;
use Chiron\View\TemplateRendererInterface;
use Chiron\View\TemplatePath;

//https://github.com/hyperf/view-engine/blob/master/src/View.php#L125
//https://github.com/hyperf/view/blob/master/src/Render.php#L90

//https://github.com/yiisoft/yii-twig/blob/master/src/ViewRenderer.php

// TODO : faire une classe abstraite avec le attributes et extension trait.
final class PhpRenderer implements TemplateRendererInterface
{
    use AttributesTrait;
    use ExtensionTrait;

    private $extension = 'phtml';

    private $context;
    private $finder;
    private $engine;

    /**
     * Create a new file view loader instance.
     *
     * @param array $paths
     * @param array $extensions
     */
    // TODO : ne passer qu'une seule extension en paramétre ----> Ne pas utiliser un tableau. Il faudra aussi retirer la méthode getPossibleViewFiles() de la classe FileViewFinder !!!!
    public function __construct(array $paths = [], ?array $extensions = null)
    {
        $this->context = new ViewContext();
        $this->engine = new PhpEngine();
        $this->finder = new FileViewFinder($paths, $extensions);
    }

    /**
     * Render a template, optionally with parameters.
     *
     * Implementations MUST support the `namespace::template` naming convention,
     * and allow omitting the filename extension.
     *
     * @param string $name
     * @param array  $params
     */
    public function render(string $name, array $params = []): string
    {
        if ($this->context->fetch('title') === '') {
            $this->assign('title', $name); //TODO : faire un Str::Humanize() ou Str::title() sur le titre.
            //https://github.com/cakephp/cakephp/blob/5.x/src/View/View.php#L829
            //https://github.com/cakephp/cakephp/blob/876a11e172b0b33710b1fbddd94de6d1618d352b/src/Utility/Inflector.php#L427
        }

        $path = $this->finder->findTemplate($name);
        $params = array_merge($this->attributes, $params);

        return $this->engine->render($this->context, $path, $params);
    }

    /**
     * Add a template path to the engine.
     *
     * Adds a template path, with optional namespace the templates in that path
     * provide.
     */
    public function addPath(string $path, ?string $namespace = null): void
    {
        $namespace = $namespace ?: FileViewFinder::DEFAULT_NAMESPACE;
        $this->finder->addPath($path, $namespace);
    }

    /**
     * Get the template directories.
     *
     * @return TemplatePath[]
     */
    public function getPaths(): array
    {
        $paths = [];
        foreach ($this->finder->getNamespaces() as $namespace) {
            $name = $namespace !== FileViewFinder::DEFAULT_NAMESPACE ? $namespace : null;
            foreach ($this->finder->getPaths($namespace) as $path) {
                $paths[] = new TemplatePath($path, $name);
            }
        }

        return $paths;
    }

    /**
     * Get the template directories.
     *
     * @return TemplatePath[]
     */
    /*
    public function getPaths() : array
    {
        $templatePaths = [];

        $paths = $this->finder->getPaths();
        $hints = $this->finder->getHints();

        foreach ($paths as $path) {
            $templatePaths[] = new TemplatePath($path);
        }
        foreach ($hints as $namespace => $paths) {
            foreach ($paths as $path) {
                $templatePaths[] = new TemplatePath($path, $namespace);
            }
        }

        return $templatePaths;
    }*/

    /**
     * Checks if the view exists.
     *
     * @param string $name View name
     *
     * @return bool True if the path exists
     */
    public function exists(string $name): bool
    {
        return $this->finder->exists($name);
    }

    /*
     * Wrapping method to redirect methods not available in this class to the
     * internal instance of the Finder class used for the rendering engine.
     * @param string $name Unknown method to call in the internal Twig rendering engine.
     * @param array $arguments Method's arguments.
     * @return mixed Result of the called method.
     */
    /*
    public function __call($name, $arguments)
    {
        call_user_func_array(array($this->finder, $name), $arguments);
    }*/

    /*
     * Wrapping method to redirect static methods not available in this class
     * to the internal instance of the Twig rendering engine.
     * @param string $name Unknown static method to call in the internal Twig rendering engine.
     * @param array $arguments Method's arguments.
     * @return mixed Result of the called static method.
     */
    /*
    public static function __callStatic($name, $arguments)
    {
        call_user_func_array(array('\\Twig_Environment', $name), $arguments);
    }*/


    /**
     * Set the content for a block. This will overwrite any
     * existing content.
     *
     * @param string $name Name of the block
     * @param string $value The content for the block.
     *
     */
    // TODO : faire un return $this pour chainer les appels ????
    public function assign(string $name, string $value): void
    {
        $this->context->assign($name, $value);
    }

}
