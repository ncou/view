<?php

declare(strict_types=1);

namespace Chiron\View\Engine;

use Throwable;

// https://github.com/yiisoft/view/blob/c81f3b910528dcefa3f02ef8a118da4fe16df218/src/WebView.php#L47
//https://github.com/cakephp/cakephp/blob/4.x/src/View/View.php


/**
 * View, the V in the MVC triad. View interacts with Helpers and view variables passed
 * in from the controller to render the results of the controller action. Often this is HTML,
 * but can also take the form of JSON, XML, PDF's or streaming files.
 *
 * CakePHP uses a two-step-view pattern. This means that the template content is rendered first,
 * and then inserted into the selected layout. This also means you can pass data from the template to the
 * layout using `$this->set()`
 *
 * View class supports using plugins as themes. You can set
 *
 * ```
 * public function beforeRender(\Cake\Event\EventInterface $event)
 * {
 *      $this->viewBuilder()->setTheme('SuperHot');
 * }
 * ```
 *
 * in your Controller to use plugin `SuperHot` as a theme. Eg. If current action
 * is PostsController::index() then View class will look for template file
 * `plugins/SuperHot/templates/Posts/index.php`. If a theme template
 * is not found for the current action the default app template file is used.
 *
 * @property \Cake\View\Helper\BreadcrumbsHelper $Breadcrumbs
 * @property \Cake\View\Helper\FlashHelper $Flash
 * @property \Cake\View\Helper\FormHelper $Form
 * @property \Cake\View\Helper\HtmlHelper $Html
 * @property \Cake\View\Helper\NumberHelper $Number
 * @property \Cake\View\Helper\PaginatorHelper $Paginator
 * @property \Cake\View\Helper\TextHelper $Text
 * @property \Cake\View\Helper\TimeHelper $Time
 * @property \Cake\View\Helper\UrlHelper $Url
 * @property \Cake\View\ViewBlock $Blocks
 */
// TODO : renommer en viewState ????
final class ViewContext
{
    /**
     * This means the location is in the head section.
     */
    public const POSITION_HEAD = 1;

    /**
     * This means the location is at the beginning of the body section.
     */
    public const POSITION_BEGIN = 2;

    /**
     * This means the location is at the end of the body section.
     */
    public const POSITION_END = 3;

    /**
     * This means the JavaScript code block will be executed when HTML document composition is ready.
     */
    public const POSITION_READY = 4;

    /**
     * This means the JavaScript code block will be executed when HTML page is completely loaded.
     */
    public const POSITION_LOAD = 5;

    private array $cssFiles;
    private array $jsFiles;

    private array $blocks;

    /**
     * An array of names of built-in helpers to include.
     *
     * @var array
     */
    protected $helpers = [];

    public function __construct()
    {
        $this->helpers['Url']['class'] = \Chiron\View\Helper\UrlHelper::class;
        $this->helpers['Html']['class'] = \Chiron\View\Helper\HtmlHelper::class;
    }

    /**
     * Magic accessor for helpers.
     *
     * @param string $name Name of the attribute to get.
     * @return \Cake\View\Helper|null
     */
    public function __get(string $name)
    {
        if (isset($this->helpers[$name]) && !isset($this->{$name})) {
            $this->{$name} = new $this->helpers[$name]['class']();

            return $this->{$name};
        }
    }

    /**
     * It processes the CSS configuration generated by the asset manager and converts it into HTML code.
     *
     * @param array $cssFiles
     */
    public function addCssFiles(array $cssFiles): void
    {
        /** @var mixed $value */
        foreach ($cssFiles as $key => $value) {
            $this->registerCssFileByConfig(
                is_string($key) ? $key : null,
                is_array($value) ? $value : [$value],
            );
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    private function registerCssFileByConfig(?string $key, array $config): void
    {
        if (!array_key_exists(0, $config)) {
            throw new InvalidArgumentException('Do not set CSS file.');
        }
        $file = $config[0];

        if (!is_string($file)) {
            throw new InvalidArgumentException(
                sprintf(
                    'CSS file should be string. Got %s.',
                    $this->getType($file),
                )
            );
        }

        $position = (int) ($config[1] ?? self::POSITION_HEAD);

        unset($config[0], $config[1]);
        $this->registerCssFile($file, $position, $config, $key);
    }

    /**
     * Registers a CSS file.
     *
     * This method should be used for simple registration of CSS files. If you want to use features of
     * {@see \Yiisoft\Assets\AssetManager} like appending timestamps to the URL and file publishing options, use
     * {@see \Yiisoft\Assets\AssetBundle}.
     *
     * @param string $url The CSS file to be registered.
     * @param array $options the HTML attributes for the link tag. Please refer to {@see \Yiisoft\Html\Html::cssFile()}
     * for the supported options.
     * @param string|null $key The key that identifies the CSS script file. If null, it will use $url as the key.
     * If two CSS files are registered with the same key, the latter will overwrite the former.
     */
    public function registerCssFile(
        string $url,
        int $position = self::POSITION_HEAD,
        array $options = [],
        string $key = null
    ): void {
        if (!$this->isValidCssPosition($position)) {
            throw new InvalidArgumentException('Invalid position of CSS file.');
        }

        //$this->cssFiles[$position][$key ?: $url] = Html::cssFile($url, $options)->render();
        $this->cssFiles[] = [$url, $options];
    }

    public function fetchCssFiles(): array
    {
        return $this->cssFiles[0];
    }

    /**
     * @param mixed $position
     *
     * @psalm-assert =int $position
     */
    // TODO : modifier le typehint car le $position sera toujours un int !!!
    private function isValidCssPosition($position): bool
    {
        return in_array(
            $position,
            [
                self::POSITION_HEAD,
                self::POSITION_BEGIN,
                self::POSITION_END,
            ],
            true,
        );
    }

    /**
     * It processes the JS configuration generated by the asset manager and converts it into HTML code.
     *
     * @param array $jsFiles
     */
    public function addJsFiles(array $jsFiles): void
    {
        /** @var mixed $value */
        foreach ($jsFiles as $key => $value) {
            $this->registerJsFileByConfig(
                is_string($key) ? $key : null,
                is_array($value) ? $value : [$value],
            );
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    private function registerJsFileByConfig(?string $key, array $config): void
    {
        if (!array_key_exists(0, $config)) {
            throw new InvalidArgumentException('Do not set JS file.');
        }
        $file = $config[0];

        if (!is_string($file)) {
            throw new InvalidArgumentException(
                sprintf(
                    'JS file should be string. Got %s.',
                    $this->getType($file),
                )
            );
        }

        $position = (int) ($config[1] ?? self::POSITION_END);

        unset($config[0], $config[1]);
        $this->registerJsFile($file, $position, $config, $key);
    }

    /**
     * Registers a JS file.
     *
     * This method should be used for simple registration of JS files. If you want to use features of
     * {@see \Yiisoft\Assets\AssetManager} like appending timestamps to the URL and file publishing options, use
     * {@see \Yiisoft\Assets\AssetBundle}.
     *
     * @param string $url The JS file to be registered.
     * @param array $options The HTML attributes for the script tag. The following options are specially handled and
     * are not treated as HTML attributes:
     *
     * - `position`: specifies where the JS script tag should be inserted in a page. The possible values are:
     *     * {@see WebView::POSITION_HEAD}: in the head section
     *     * {@see WebView::POSITION_BEGIN}: at the beginning of the body section
     *     * {@see WebView::POSITION_END}: at the end of the body section. This is the default value.
     *
     * Please refer to {@see \Yiisoft\Html\Html::javaScriptFile()} for other supported options.
     * @param string|null $key The key that identifies the JS script file. If null, it will use $url as the key.
     * If two JS files are registered with the same key at the same position, the latter will overwrite the former.
     * Note that position option takes precedence, thus files registered with the same key, but different
     * position option will not override each other.
     */
    public function registerJsFile(
        string $url,
        int $position = self::POSITION_END,
        array $options = [],
        string $key = null
    ): void {
        if (!$this->isValidJsPosition($position)) {
            throw new InvalidArgumentException('Invalid position of JS file.');
        }

        //$this->jsFiles[$position][$key ?: $url] = Html::javaScriptFile($url, $options)->render();
        $this->jsFiles[] = [$url, $options];
    }

    /**
     * @param mixed $position
     *
     * @psalm-assert =int $position
     */
    // TODO : modifier le typehint car le $position sera toujours un int !!!
    private function isValidJsPosition($position): bool
    {
        return in_array(
            $position,
            [
                self::POSITION_HEAD,
                self::POSITION_BEGIN,
                self::POSITION_END,
                self::POSITION_READY,
                self::POSITION_LOAD,
            ],
            true,
        );
    }

    /**
     * It processes the JS configuration generated by the asset manager and converts it into HTML code.
     *
     * @param array $jsFiles
     */
    public function fetchJsFiles(): array
    {
        return $this->jsFiles[0];
    }

    /**
     * @param mixed $value
     */
    // TODO : utiliser la méthode debug_type() pour récupérer le type !!!
    private function getType($value): string
    {
        return is_object($value) ? get_class($value) : gettype($value);
    }



    /**
     * Set the content for a block. This will overwrite any
     * existing content.
     *
     * @param string $name Name of the block
     * @param string $value The content for the block.
     *
     */
    // TODO : faire un return $this pour chainer les appels ????
    public function assign(string $name, string $value): void
    {
        $this->blocks[$name] = $value;
    }

    /**
     * Fetch the content for a block. If a block is
     * empty or undefined '' will be returned.
     *
     * @param string $name Name of the block
     * @param string $default Default text
     * @return string The block content or $default if the block does not exist.
     * @see \Cake\View\ViewBlock::get()
     */
    public function fetch(string $name, string $default = ''): string
    {
        return $this->blocks[$name] ?? $default;
    }
}
