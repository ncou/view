<?php

declare(strict_types=1);

namespace Chiron\View\Tests\Fixtures;

class TemplateRendererMock implements \Chiron\View\TemplateRendererInterface
{
    use \Chiron\View\AttributesTrait;
    use \Chiron\View\ExtensionTrait;

    public function render(string $name, array $params = []): string
    {
        return 'mock';
    }

    public function addPath(string $path, string $namespace = null): void
    {
    }

    public function getPaths(): array
    {
        return [];
    }

    public function exists(string $name): bool
    {
        return true;
    }
}
