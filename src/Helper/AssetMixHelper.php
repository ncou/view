<?php

declare(strict_types=1);

namespace Chiron\View\Helper;

use Chiron\View\Traits\HelperAccessorTrait;

//https://github.com/ishanvyas22/asset-mix/blob/master/src/View/Helper/AssetMixHelper.php

final class AssetMixHelper //extends Helper
{
    use HelperAccessorTrait;

    /**
     * List of helpers used by this helper
     *
     * @var array
     */
    protected array $helpers = [
        'Html' => HtmlHelper::class,
        'Url'  => UrlHelper::class,
    ];

    /**
     * Creates a link element for CSS stylesheets with versioned asset.
     *
     * @param string       $path    Path to css file.
     * @param array<mixed> $options Options array.
     *
     * @return string|null CSS `<link />` or `<style />` tag, depending on the type of link.
     */
    // TODO : faire en sorte qu'il y ait jamais un retour à NULL + changer le return typehint
    // TODO : reprendre la même phpdoc que celle de HtmlHelper->css()
    public function css(string $path, array $options = []): ?string
    {
        // Get css file path, add extension if not provided, skip if url provided
        if (strpos($path, '//') !== false) {
            return $this->Html->css($path, $options);
        }

        $url = $this->Url->css($path, ['timestamp' => false]);

        // Pass proper filename with path to mix common function
        $mixPath = $this->resolvePath($url);

        return $this->Html->css($mixPath, $options);
    }

    /**
     * Returns one or many `<script>` tags depending on the number of scripts given.
     *
     * @param string       $url     String or array of javascript files to include
     * @param array<mixed> $options Array of options, and html attributes see above.
     *
     * @return string|null String of `<script />` tags or null if block is specified in options
     *   or if $once is true and the file has been included before.
     */
    // TODO : faire en sorte qu'il y ait jamais un retour à NULL + changer le return typehint
    // TODO : reprendre la même phpdoc que celle de HtmlHelper->script()
    public function script(string $url, array $options = []): ?string
    {
        $defaults = ['defer' => true]; // TODO : à virer cela ne sert à rien !!!!
        $options += $defaults;

        // Get css file path, add extension if not provided, skip if url provided
        if (strpos($url, '//') !== false) {
            return $this->Html->script($url, $options);
        }

        $url = $this->Url->script($url, ['timestamp' => false]);

        // Pass proper filename with path to mix common function
        $mixPath = $this->resolvePath($url);

        return $this->Html->script($mixPath, $options);
    }

    /**
     * Remove the subfolder in the url to acces the real file.
     * Use the Laravel mix manifest file to retrieve the targeted url.
     */
    private function resolvePath(string $url): string
    {
        $basePath = config('http')->get('base_path');

        $filepath = preg_replace(
            '/^' . preg_quote($basePath, '/') . '/',
            '',
            urldecode($url)
        );

        return (new Mix())($filepath);
    }
}
