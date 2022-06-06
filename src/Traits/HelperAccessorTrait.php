<?php

declare(strict_types=1);

namespace Chiron\View\Traits;

trait HelperAccessorTrait
{
    /**
     * List of helpers used by this helper
     *
     * @var array
     */
    //protected array $helpers = [];

    /**
     * Lazy loads helpers.
     *
     * @param string $name Name of the property being accessed.
     *
     * @return mixed If helper with provided name exists
     */
    public function __get(string $name): mixed
    {
        if (isset($this->helpers[$name]) && ! isset($this->{$name})) {
            $this->{$name} = new $this->helpers[$name]();

            return $this->{$name};
        }
    }
}
