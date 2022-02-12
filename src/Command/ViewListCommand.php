<?php

declare(strict_types=1);

namespace Chiron\View\Command;

use Chiron\Filesystem\Filesystem;
use Chiron\Core\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputOption;

use InvalidArgumentException;
use RuntimeException;
use LogicException;

use Twig\Environment;
use Twig\Error\Error as TwigErrorException;
use Twig\Loader\ArrayLoader;
use Twig\Source;
use Twig\Loader\FilesystemLoader;
use Chiron\View\TemplateRendererInterface;

// TODO : virer les références à TWIG !!!!

final class ViewListCommand extends AbstractCommand
{
    /** @var \Twig\Environment */
    private $twig;

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
            if (!$firstNamespace && !$prevHasSeparator && count($paths) > 1) {
                $rows[] = ['', ''];
            }
            $firstNamespace = false;
            foreach ($paths as $path) {
                $rows[] = [$namespace, $path.DIRECTORY_SEPARATOR];
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
