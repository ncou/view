<?php

declare(strict_types=1);

namespace Chiron\View\Native;

use Chiron\View\ViewContext;
use Chiron\View\ViewInterface;

//use Chiron\View\Engine\PhpEngine;

// TODO : ajouter une méthode setParameters() ???? Ca permettrait de faire le merge des paramétres plus haut dans le code (dans la classe Engine ou ViewManager par exemple)
//https://github.com/yiisoft/view/blob/12b99ef27605f615608b1ef31f3e887968d0c033/src/ViewInterface.php#L139
//https://github.com/yiisoft/yii-view/blob/406384e8a54c98e6aeb519356cb2108dff646134/src/ViewRenderer.php#L338
//https://github.com/yiisoft/view/blob/12b99ef27605f615608b1ef31f3e887968d0c033/src/State/StateTrait.php#L91

final class NativeView implements ViewInterface
{
    private $path;
    private $context;
    private $parameters;

    /**
     * Create a new file view loader instance.
     *
     * @param array $paths
     * @param array $extensions
     */
    // TODO : ne passer qu'une seule extension en paramétre ----> Ne pas utiliser un tableau. Il faudra aussi retirer la méthode getPossibleViewFiles() de la classe FileViewFinder !!!!
    // TODO : passer le viewcontext ???
    // TODO : créer une méthode setParameters() et getParameters() ????
    public function __construct(string $path, array $parameters = [])
    {
        $this->path = $path;
        $this->parameters = $parameters;

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
    //https://github.com/yiisoft/view/blob/master/src/PhpTemplateRenderer.php
    //https://github.com/yiisoft/yii-twig/blob/master/src/ViewRenderer.php
    //https://github.com/hyperf/view-engine/blob/master/src/Engine/PhpEngine.php#L58
    //https://github.com/cakephp/cakephp/blob/4.x/src/View/View.php#L1172
    //https://github.com/spiral/views/blob/9e78375b2618ab2500b250e6983e6593dcc8d0d9/src/Engine/Native/NativeView.php#L21
    // TODO : garder l'initialisation à un tableau vide pour le paramétre $parameters ???
    public function render(array $parameters = []): string
    {
        // TODO : Utiliser une méthode plus poussée (cad en évitant d'écraser les valeurs) pour le merge ? https://github.com/yiisoft/yii-view/blob/master/src/ViewRenderer.php#L330
        $parameters = array_merge($this->parameters, $parameters);

        // No named parameters for this function due to possible collision with extract() vars.
        $renderer = function (): void {
            /** @psalm-suppress MixedArgument */
            extract(func_get_arg(1), EXTR_OVERWRITE);
            /** @psalm-suppress UnresolvableInclude */
            require func_get_arg(0);
        };

        $levels = ob_get_level(); // TODO : renommer la variable en $level : https://github.com/thephpleague/plates/blob/v3/src/Template/Template.php#L167
        ob_start();
        ob_implicit_flush(false);

        try {
            /** @psalm-suppress PossiblyInvalidFunctionCall */
            $renderer->bindTo($this->context)($this->path, $parameters); // TODO : stocker le retour dans une variable $content et faire un return $content à la fin de la fonction ???
        } catch (\Throwable $e) {
            // TODO : c'est > ou >= qu'il faut utiliser ???
            while (ob_get_level() > $levels) {
                ob_end_clean();
            }

            // TODO : créer une RenderException : https://github.com/spiral/views/blob/9e78375b2618ab2500b250e6983e6593dcc8d0d9/src/Exception/RenderException.php#L14
            throw $e;
        }
        // TODO : ajouter un finaly ??? https://github.com/spiral/views/blob/5d2123adc3cca2dc3e3c4ca0b9fe77d5ab2bf660/src/Engine/Native/NativeView.php#L59

        return ob_get_clean();
    }

    /**
     * Set the content for a block. This will overwrite any existing content.
     *
     * @param string $name  Name of the block.
     * @param string $value The content for the block.
     */
    public function assign(string $name, string $value): self
    {
        $this->context->assign($name, $value);

        return $this;
    }

    /**
     * Define a helper with an alias and associated class name.
     *
     * @param string $alias  Alias used to access the helper.
     * @param string $class Class name for the helper.
     */
    public function helper(string $alias, string $class): self
    {
        $this->context->helper($alias, $class);

        return $this;
    }

    // TODO : ajouter une méthode pour accéder au context ???? genre un "getContext(): ViewContext" ???

    // TODO : faire des méthodes proxy pour appeller $this->context->addCssFiles() et $this->context->registerCssFile() + idem pour les scripts. Cela permettra d'enregistrer directement depuis le controller les assets css & scripts
}
