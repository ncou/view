<?php

declare(strict_types=1);

namespace Chiron\View;

trait ExtensionTrait
{
    //protected $extension = 'html';

    public function getExtension(): string
    {
        return $this->extension;
    }

    public function setExtension(string $extension): TemplateRendererInterface
    {
        $this->extension = $extension;

        return $this;
    }
}
