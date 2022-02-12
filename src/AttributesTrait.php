<?php

declare(strict_types=1);

namespace Chiron\View;

trait AttributesTrait
{
    private $attributes = [];

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function setAttributes(array $attributes): TemplateRendererInterface
    {
        $this->attributes = $attributes;

        return $this;
    }

    public function unsetAttributes(): TemplateRendererInterface
    {
        $this->setAttributes([]);

        return $this;
    }

    /**
     * @param string $key
     * @param mixed  $value
     */
    public function addAttribute(string $key, $value): TemplateRendererInterface
    {
        $this->attributes[$key] = $value;

        return $this;
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function getAttribute(string $key)
    {
        return $this->attributes[$key];
    }

    public function removeAttribute(string $key): TemplateRendererInterface
    {
        unset($this->attributes[$key]);

        return $this;
    }

    public function hasAttribute(string $key): bool
    {
        return array_key_exists($key, $this->attributes);
    }
}
