<?php

declare(strict_types=1);

namespace Chiron\View\Helper;

use Chiron\ResponseCreator\ResponseCreator;
use Chiron\View\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;

// TODO : passer la classe en Abstract ????
class Helper
{
     /**
     * List of helpers used by this helper
     *
     * @var array
     */
    protected $helpers = [];

    /**
     * A helper lookup table used to lazy load helper objects.
     *
     * @var array<string, array>
     */
    protected $_helperMap = [];

    /**
     * Default Constructor
     *
     * @param \Cake\View\View $view The View this helper is being attached to.
     * @param array<string, mixed> $config Configuration settings for the helper.
     */
    public function __construct()
    {
        if (!empty($this->helpers)) {
            //$this->_helperMap = $view->helpers()->normalizeArray($this->helpers);
            $this->_helperMap['Url']['class'] = \Chiron\View\Helper\UrlHelper::class;
        }
    }

    /**
     * Provide non fatal errors on missing method calls.
     *
     * @param string $method Method to invoke
     * @param array $params Array of params for the method.
     * @return mixed|void
     */
    // TODO : c'est vraiment utile ce trigger ???
    public function __call(string $method, array $params)
    {
        trigger_error(sprintf('Method %1$s::%2$s does not exist', static::class, $method), E_USER_WARNING);
    }

    /**
     * Lazy loads helpers.
     *
     * @param string $name Name of the property being accessed.
     * @return \Cake\View\Helper|null|void Helper instance if helper with provided name exists
     */
    public function __get(string $name)
    {
        if (isset($this->_helperMap[$name]) && !isset($this->{$name})) {
            //$config = ['enabled' => false] + (array)$this->_helperMap[$name]['config'];
            //$this->{$name} = $this->_View->loadHelper($this->_helperMap[$name]['class'], $config);
            $this->{$name} = new $this->_helperMap[$name]['class']();

            return $this->{$name};
        }
    }


}
