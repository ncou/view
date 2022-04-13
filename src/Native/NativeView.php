<?php

declare(strict_types=1);

namespace Chiron\View\Native;

use Chiron\View\ViewInterface;
use Chiron\View\ViewContext;
//use Chiron\View\Engine\PhpEngine;

final class NativeView implements ViewInterface
{
    private $path;
    private $context;
    //private $engine;

    /**
     * Create a new file view loader instance.
     *
     * @param array $paths
     * @param array $extensions
     */
    // TODO : ne passer qu'une seule extension en paramétre ----> Ne pas utiliser un tableau. Il faudra aussi retirer la méthode getPossibleViewFiles() de la classe FileViewFinder !!!!
    // TODO : passer le viewcontext ???
    public function __construct(string $path)
    {
        $this->path = $path;

        $this->context = new ViewContext();
        //$this->engine = new PhpEngine();
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
    public function render(array $params = []): string
    {
        if ($this->context->fetch('title') === '') {
            $this->assign('title', $this->path); //TODO : faire un Str::Humanize() ou Str::title() sur le titre. ou alors utiliser une classe inflector !!!!
            //https://github.com/cakephp/cakephp/blob/5.x/src/View/View.php#L829
            //https://github.com/cakephp/cakephp/blob/876a11e172b0b33710b1fbddd94de6d1618d352b/src/Utility/Inflector.php#L427
        }

        return $this->renderInternal($this->context, $this->path, $params); // TODO : reporter le code du PhPEngine->render() directement ici dans cette classe !!!! et virer ensuite la classe PhpEngine !!!
    }

    //https://github.com/yiisoft/view/blob/master/src/PhpTemplateRenderer.php
    //https://github.com/yiisoft/yii-twig/blob/master/src/ViewRenderer.php
    //https://github.com/hyperf/view-engine/blob/master/src/Engine/PhpEngine.php#L58
    //https://github.com/cakephp/cakephp/blob/4.x/src/View/View.php#L1172
    //https://github.com/spiral/views/blob/9e78375b2618ab2500b250e6983e6593dcc8d0d9/src/Engine/Native/NativeView.php#L21
    // TODO : garder l'initialisation à un tableau vide pour le paramétre $parameters ???
    private function renderInternal(ViewContext $view, string $template, array $parameters = []): string
    {
        $renderer = function (): void {
            /** @psalm-suppress MixedArgument */
            extract(func_get_arg(1), EXTR_OVERWRITE);
            /** @psalm-suppress UnresolvableInclude */
            require func_get_arg(0);
        };

        $obInitialLevel = ob_get_level(); // TODO : renommer la variable en $level : https://github.com/thephpleague/plates/blob/v3/src/Template/Template.php#L167
        ob_start();
        ob_implicit_flush(false);

        try {
            /** @psalm-suppress PossiblyInvalidFunctionCall */
            $renderer->bindTo($view)($template, $parameters); // TODO : stocker le retour dans une variable $content et faire un return $content à la fin de la fonction ???

            return ob_get_clean(); // TODO : déplacer ce return à la fin de la méthode ??? https://github.com/cakephp/cakephp/blob/4.x/src/View/View.php#L1190
        } catch (Throwable $e) {
            // TODO : c'est > ou >= qu'il faut utiliser ???
            while (ob_get_level() > $obInitialLevel) {
                ob_end_clean();
            }
            // TODO : créer une RenderException : https://github.com/spiral/views/blob/9e78375b2618ab2500b250e6983e6593dcc8d0d9/src/Exception/RenderException.php#L14
            throw $e;
        }
        // TODO : ajouter un finaly ??? https://github.com/spiral/views/blob/5d2123adc3cca2dc3e3c4ca0b9fe77d5ab2bf660/src/Engine/Native/NativeView.php#L59
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
    public function assign(string $name, string $value): self
    {
        $this->context->assign($name, $value);

        return $this;
    }

    // TODO : faire des méthodes proxy pour appeller $this->context->addCssFiles() et $this->context->registerCssFile() + idem pour les scripts. Cela permettra d'enregistrer directement depuis le controller les assets css & scripts

}
