<?php

declare(strict_types=1);

namespace Chiron\View;

interface ViewInterface
{
    /**
     * Render view source using internal logic.
     *
     * @throws RenderException
     */
    public function render(array $parameters = []): string;

    /**
     * Set the content for a block. This will overwrite any existing content.
     *
     * @param string $name  Name of the block.
     * @param string $value The content for the block.
     */
    public function assign(string $name, string $value): ViewInterface;

    /**
     * Define a helper with an alias and associated class name.
     *
     * @param string $alias  Alias used to access the helper.
     * @param string $class Class name for the helper.
     */
    public function helper(string $alias, string $class): ViewInterface;
}
