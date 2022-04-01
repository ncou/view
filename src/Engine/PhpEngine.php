<?php

declare(strict_types=1);

namespace Chiron\View\Engine;

use Throwable;

//https://github.com/yiisoft/view/blob/master/src/PhpTemplateRenderer.php
//https://github.com/yiisoft/yii-twig/blob/master/src/ViewRenderer.php

//https://github.com/hyperf/view-engine/blob/master/src/Engine/PhpEngine.php#L58

//https://github.com/cakephp/cakephp/blob/4.x/src/View/View.php#L1172

//https://github.com/spiral/views/blob/9e78375b2618ab2500b250e6983e6593dcc8d0d9/src/Engine/Native/NativeView.php#L21

final class PhpEngine
{
    // TODO : garder l'initialisation à un tableau vide pour le paramétre $parameters ???
    public function render(ViewContext $view, string $template, array $parameters = []): string
    {
        $renderer = function (): void {
            /** @psalm-suppress MixedArgument */
            extract(func_get_arg(1), EXTR_OVERWRITE);
            /** @psalm-suppress UnresolvableInclude */
            require func_get_arg(0);
        };

        $obInitialLevel = ob_get_level();
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
    }


    public function render_SAVE(string $sourceFile, array $variables = []): string
    {
        // TODO : ce if ne sert à rien car on vérifie en amont que le template existe bien !!!! donc à virer !!!!
        if (! is_file($sourceFile)) {
            // TODO : utiliser un sprintf !!!!
            // https://github.com/cakephp/cakephp/blob/4.x/src/View/Exception/MissingTemplateException.php
            // https://github.com/yiisoft/view/blob/master/src/Exception/ViewNotFoundException.php
            throw new \InvalidArgumentException("Unable to render template : `$sourceFile` because this file does not exist");
        }

        // It's to prevent common problems with paths associated with symlinks
        //$sourceFile = realpath($sourceFile);

        // We'll evaluate the contents of the view inside a try/catch block so we can
        // flush out any stray output that might get out before an error occurs or
        // an exception is thrown. This prevents any partial views from leaking.
        try {
            ob_start();
            call_user_func(function () {
                extract(func_get_arg(0), EXTR_OVERWRITE); // EXTR_SKIP
                include func_get_arg(1);
            }, $variables, $sourceFile);
            $content = ob_get_clean();
        } catch (Throwable $e) {
            ob_end_clean();

            throw $e;
        }

        return $content;
    }

}
