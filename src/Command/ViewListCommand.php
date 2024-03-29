<?php

declare(strict_types=1);

namespace Chiron\View\Command;

use Chiron\Core\Command\AbstractCommand;
use Chiron\Filesystem\Filesystem;
use Chiron\View\TemplateRendererInterface;
use Twig\Environment;

//https://github.com/spiral/framework/blob/23299ff3442a9334494b9481b9adbd2b4a317907/src/Framework/Command/Views/CompileCommand.php#L133

// TODO : virer les références à TWIG !!!!

final class ViewListCommand extends AbstractCommand
{
    /** @var \Twig\Environment */
    private Environment $twig;

    protected static $defaultName = 'view:list';

    protected function configure(): void
    {
        $this->setDescription('List the registered view paths and associated namespaces.');
    }

    // TODO : virer le paramétre Filesystem $filesystem qui ne sert à rien !!!!
    public function perform(Filesystem $filesystem, TemplateRendererInterface $renderer): int
    {
        // TODO : gérer le cas ou le tableau de $paths est vide et dans ce cas afficher le message : 'No template paths configured for your application.'
        $paths = $this->getPaths($renderer);

        $this->newline();
        $this->notice('View Paths & Namespaces');
        //$this->newline();
        $table = $this->table(['Namespace', 'Path(s)'], $this->buildTableRows($paths));

        $table->render();

        return self::SUCCESS;
    }

    private function getPaths(TemplateRendererInterface $renderer): array
    {
        $loaderPaths = [];
        foreach ($renderer->getPaths() as $templatePath) {
            $path = $templatePath->getPath();
            $namespace = $templatePath->getNamespace();

            // TODO : utiliser une constante pour définir quand la namespace est par défault à null (cf exemple de Twig\FilesystemLoader::MAIN_NAMESPACE)
            if ($namespace === null) {
                $namespace = '(None)';
            }

            $loaderPaths[$namespace] = array_merge($loaderPaths[$namespace] ?? [], [$path]);
        }

        return $loaderPaths;
    }

    private function buildTableRows(array $loaderPaths): array
    {
        $rows = [];
        $firstNamespace = true;
        $prevHasSeparator = false;

        foreach ($loaderPaths as $namespace => $paths) {
            if (! $firstNamespace && ! $prevHasSeparator && count($paths) > 1) {
                $rows[] = ['', ''];
            }
            $firstNamespace = false;
            foreach ($paths as $path) {
                $rows[] = [$namespace, $path . DIRECTORY_SEPARATOR];
                $namespace = '';
            }
            if (count($paths) > 1) {
                $rows[] = ['', ''];
                $prevHasSeparator = true;
            } else {
                $prevHasSeparator = false;
            }
        }
        if ($prevHasSeparator) {
            array_pop($rows);
        }

        return $rows;
    }
}
