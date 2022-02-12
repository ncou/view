<?php

declare(strict_types=1);

namespace Chiron\View\Bootloader;

use Chiron\Core\Container\Bootloader\AbstractBootloader;
use Chiron\Views\Config\ViewsConfig;
use Chiron\Views\TemplateRendererInterface;
use Chiron\Core\Directories;

//https://github.com/spiral/framework/blob/master/src/Framework/Bootloader/Views/ViewsBootloader.php#L47

final class ViewBootloader extends AbstractBootloader
{
    public function boot(TemplateRendererInterface $renderer, ViewsConfig $config): void
    {

// TODO : ne pas définir le répertoire views dans le paths.php et ne pas faire de merge dans le DirectoryBootloader, mais l'ajouter ici lors de ce bootloader !!!! Il faudra ensuite nettoyer le paths.php de l'appli en ligne de commande "app-cli" pour retirer la ligne "views" car cela n'a pas de sens de le définir alors qu'on est sur une appli en ligne de commandes.
        /*
        if (! $directories->has('@views')) {
            $directories->set('views', $directories->get('@resources/views/');
        }*/


        // Force file extension to use (only if not null).
        if ($extension = $config->getExtension()) {
            $renderer->setExtension($extension);
        }

        // add template paths
        foreach ($config->getPaths() as $namespace => $paths) {
            // TODO : créer une constante EMPTY_NAMESPACE dans la classe TemplateRenderInterface ??? ca serai plus propre que d'utiliser directement "null" dans le code ci dessous !!!
            $namespace = is_int($namespace) ? null : $namespace;

            foreach ((array) $paths as $path) {
                $renderer->addPath($path, $namespace);
            }
        }

        // TODO : ca serait bien de récupérer aussi la classe HttpConfig (attention à voir si il n'y a pas un risque que cette classe n'existe pas quand on aura séparé les packages http et chiron, car il y a un risque que l'utilisateur install chiron+le package template mais pas le package http, mais je pense que ce risque est faible !!!!) pour injecter dans les variables Globals (attributes) du TemplateRendererInterface la valeur du "base_path" car on pourrait en avoir besoin dans les templates twig ou autre pour construire une URL (on utiliserai donc la notation : { base_path ~ '/assets/image.png'}) par exemple. De mémoire c'est ce que fait Drupal !!!
    }
}
