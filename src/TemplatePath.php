<?php

declare(strict_types=1);

namespace Chiron\View;

// TODO : il faudrait pas créer une méthode hasNamespace(): bool ????
class TemplatePath
{
    /**
     * @var string
     */
    protected $path;

    /**
     * @var null|string
     */
    protected $namespace;

    public function __construct(string $path, string $namespace = null)
    {
        $this->path = $path;
        $this->namespace = $namespace;
    }

    /**
     * Get the namespace.
     *
     * @return null|string Return the namespace if it exists, else we return null.
     */
    public function getNamespace(): ?string
    {
        return $this->namespace;
    }

    /**
     * Get the path.
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Casts to string by returning the path only.
     */
    public function __toString(): string
    {
        return $this->path;
    }
}
