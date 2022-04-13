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
    public function render(array $data = []): string;
}
