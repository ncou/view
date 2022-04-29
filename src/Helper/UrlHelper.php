<?php

declare(strict_types=1);

namespace Chiron\View\Helper;

use Chiron\ResponseCreator\ResponseCreator;
use Chiron\View\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;

// TODO : créer un helper dédié aux tableaux !!!! https://github.com/JelmerD/TableHelper/blob/master/src/View/Helper/TableHelper.php

//https://github.com/cakephp/cakephp/blob/32e3c532fea8abe2db8b697f07dfddf4dfc134ca/tests/TestCase/Routing/AssetTest.php
//https://book.cakephp.org/4/en/views/helpers/url.html#Cake\View\Helper\UrlHelper::build

// TODO : passer les protected en private car la classe est final !!!!
final class UrlHelper
{

    /**
     * Returns a URL based on provided parameters.
     *
     * ### Options:
     *
     * - `escape`: If false, the URL will be returned unescaped, do only use if it is manually
     *    escaped afterwards before being displayed.
     * - `fullBase`: If true, the full base URL will be prepended to the result
     *
     * @param array|string|null $url Either a relative string URL like `/products/view/23` or
     *    an array of URL parameters. Using an array for URLs will allow you to leverage
     *    the reverse routing features of CakePHP.
     * @param array<string, mixed> $options Array of options.
     * @return string Full translated URL with base path.
     */
    // TODO : code temporaire, attention cela ne fonctionne pas si on a un lien du style http://xxxx donc il ne faut pas concaténer par défaut le http.basePath !!!! https://github.com/cakephp/cakephp/blob/856741f34393bef25284b86da703e840071c4341/src/Routing/Router.php#L501

    // TODO : Virer la possibilité de passer un tableau pour le paramétre $url !!!!
    // TODO : pourquoi on peut passer null comme valeur au paramétre $url ????
    public function build(string $url, array $options = []): string
    {
        $defaults = [
            'fullBase' => false,
            'escape' => true,
        ];
        $options += $defaults;

        $url = $this->buildUrl($url, $options['fullBase']);
        if ($options['escape']) {
            /** @var string $url */
            $url = e($url);
        }

        return $url;
    }

    /**
     * Finds URL for specified action.
     *
     * Returns a URL pointing to a combination of controller and action.
     *
     * ### Usage
     *
     * - `Router::url('/posts/edit/1');` Returns the string with the base dir prepended.
     *   This usage does not use reverser routing.
     * - `Router::url(['controller' => 'Posts', 'action' => 'edit']);` Returns a URL
     *   generated through reverse routing.
     * - `Router::url(['_name' => 'custom-name', ...]);` Returns a URL generated
     *   through reverse routing. This form allows you to leverage named routes.
     *
     * There are a few 'special' parameters that can change the final URL string that is generated
     *
     * - `_base` - Set to false to remove the base path from the generated URL. If your application
     *   is not in the root directory, this can be used to generate URLs that are 'cake relative'.
     *   cake relative URLs are required when using requestAction.
     * - `_scheme` - Set to create links on different schemes like `webcal` or `ftp`. Defaults
     *   to the current scheme.
     * - `_host` - Set the host to use for the link. Defaults to the current host.
     * - `_port` - Set the port if you need to create links on non-standard ports.
     * - `_full` - If true output of `Router::fullBaseUrl()` will be prepended to generated URLs.
     * - `#` - Allows you to set URL hash fragments.
     * - `_ssl` - Set to true to convert the generated URL to https, or false to force http.
     * - `_name` - Name of route. If you have setup named routes you can use this key
     *   to specify it.
     *
     * @param \Psr\Http\Message\UriInterface|array|string|null $url An array specifying any of the following:
     *   'controller', 'action', 'plugin' additionally, you can provide routed
     *   elements or query string parameters. If string it can be name any valid url
     *   string or it can be an UriInterface instance.
     * @param bool $full If true, the full base URL will be prepended to the result.
     *   Default is false.
     * @return string Full translated URL with base path.
     * @throws \Cake\Core\Exception\CakeException When the route name is not found
     */
    // TODO : code temporaire, attention cela ne fonctionne pas si on a un lien du style http://xxxx donc il ne faut pas concaténer par défaut le http.basePath !!!!
    //https://github.com/cakephp/cakephp/blob/856741f34393bef25284b86da703e840071c4341/src/Routing/Router.php#L501

    // TODO : Virer la possibilité de passer un tableau pour le paramétre $url !!!!
    // TODO : pourquoi on peut passer null comme valeur au paramétre $url ????
    // TODO : corriger le phpdoc pour le paramétre $url !!!
    private function buildUrl(string $url, bool $full = false): string
    {
        $plainString = (
            strpos($url, 'javascript:') === 0 ||
            strpos($url, 'mailto:') === 0 ||
            strpos($url, 'tel:') === 0 ||
            strpos($url, 'sms:') === 0 ||
            strpos($url, '#') === 0 ||
            strpos($url, '?') === 0 ||
            strpos($url, '//') === 0 ||
            strpos($url, '://') !== false
        );

        if ($plainString) {
            return $url;
        }

        $context['_base'] = ''; // TODO : code temporaire !!! il va falloir aller chercher le basepath/prefix dans le fichier de config html !!!! et renommer la variable en $prefix
        $output = $context['_base'] . $url;

        $protocol = preg_match('#^[a-z][a-z0-9+\-.]*\://#i', $output);
        if ($protocol === 0) {
            $output = str_replace('//', '/', '/' . $output);
            if ($full) {
                $output = static::fullBaseUrl() . $output;
            }
        }

        return $output;
    }

    /**
     * Generates URL for given image file.
     *
     * Depending on options passed provides full URL with domain name. Also calls
     * `Helper::assetTimestamp()` to add timestamp to local files.
     *
     * @param string $path Path string.
     * @param array<string, mixed> $options Options array. Possible keys:
     *   `fullBase` Return full URL with domain name
     *   `pathPrefix` Path prefix for relative URLs
     *   `plugin` False value will prevent parsing path as a plugin
     *   `timestamp` Overrides the value of `Asset.timestamp` in Configure.
     *        Set to false to skip timestamp generation.
     *        Set to true to apply timestamps when debug is true. Set to 'force' to always
     *        enable timestamping regardless of debug value.
     * @return string Generated URL
     */
    public function image(string $path, array $options = []): string
    {
        $pathPrefix = 'assets/img/'; //Configure::read('App.imageBaseUrl');

        return e($this->assetUrlInternal($path, $options + compact('pathPrefix')));
    }

    /**
     * Generates URL for given CSS file.
     *
     * Depending on options passed provides full URL with domain name. Also calls
     * `Helper::assetTimestamp()` to add timestamp to local files.
     *
     * @param string $path Path string.
     * @param array<string, mixed> $options Options array. Possible keys:
     *   `fullBase` Return full URL with domain name
     *   `pathPrefix` Path prefix for relative URLs
     *   `ext` Asset extension to append
     *   `plugin` False value will prevent parsing path as a plugin
     *   `timestamp` Overrides the value of `Asset.timestamp` in Configure.
     *        Set to false to skip timestamp generation.
     *        Set to true to apply timestamps when debug is true. Set to 'force' to always
     *        enable timestamping regardless of debug value.
     * @return string Generated URL
     */
    public function css(string $path, array $options = []): string
    {
        $pathPrefix = 'assets/css/'; //Configure::read('App.cssBaseUrl');
        $ext = '.css';

        return e($this->assetUrlInternal($path, $options + compact('pathPrefix', 'ext')));
    }

    /**
     * Generates URL for given javascript file.
     *
     * Depending on options passed provides full URL with domain name. Also calls
     * `Helper::assetTimestamp()` to add timestamp to local files.
     *
     * @param string $path Path string.
     * @param array<string, mixed> $options Options array. Possible keys:
     *   `fullBase` Return full URL with domain name
     *   `pathPrefix` Path prefix for relative URLs
     *   `ext` Asset extension to append
     *   `plugin` False value will prevent parsing path as a plugin
     *   `timestamp` Overrides the value of `Asset.timestamp` in Configure.
     *        Set to false to skip timestamp generation.
     *        Set to true to apply timestamps when debug is true. Set to 'force' to always
     *        enable timestamping regardless of debug value.
     * @return string Generated URL
     */
    public function script(string $path, array $options = []): string
    {
        $pathPrefix = 'assets/js/'; //Configure::read('App.jsBaseUrl');
        $ext = '.js';

        return e($this->assetUrlInternal($path, $options + compact('pathPrefix', 'ext')));
    }


    /**
     * Generates URL for given asset file.
     *
     * Depending on options passed provides full URL with domain name. Also calls
     * `Helper::assetTimestamp()` to add timestamp to local files.
     *
     * ### Options:
     *
     * - `fullBase` Boolean true or a string (e.g. https://example) to
     *    return full URL with protocol and domain name.
     * - `pathPrefix` Path prefix for relative URLs
     * - `ext` Asset extension to append
     * - `plugin` False value will prevent parsing path as a plugin
     * - `timestamp` Overrides the value of `Asset.timestamp` in Configure.
     *    Set to false to skip timestamp generation.
     *    Set to true to apply timestamps when debug is true. Set to 'force' to always
     *    enable timestamping regardless of debug value.
     *
     * @param string $path Path string or URL array
     * @param array<string, mixed> $options Options array.
     * @return string Generated URL
     */
    // TODO : virer la notion de plugin
    public function assetUrl(string $path, array $options = []): string
    {
        return e($this->assetUrlInternal($path, $options));
    }

    private function assetUrlInternal(string $path, array $options = []): string
    {
        if (preg_match('/^data:[a-z]+\/[a-z]+;/', $path)) {
            return $path;
        }

        if (strpos($path, '://') !== false || preg_match('/^[a-z]+:/i', $path)) {
            //return ltrim(Router::url($path), '/');
            //return ltrim($this->routerUrl($path), '/'); // TODO : je pense qu'il suffit de retourner le $path plutot que de passer par cette méthode routerUrl() !!!
            return $path;
        }

/*
        if (!array_key_exists('plugin', $options) || $options['plugin'] !== false) {
            [$plugin, $path] = static::pluginSplit($path);
        }
*/

        // TODO : vérifier pourquoi on fait dans ce IF un controle sur la condition :    $path[0] !== '/'
        if (!empty($options['pathPrefix']) && $path[0] !== '/') {
            /*
            $pathPrefix = $options['pathPrefix'];

            $placeHolderVal = '';
            if (!empty($options['theme'])) {
                $placeHolderVal = static::inflectString($options['theme']) . '/';
            } elseif (isset($plugin)) {
                $placeHolderVal = static::inflectString($plugin) . '/';
            }

            $path = str_replace('{plugin}', $placeHolderVal, $pathPrefix) . $path; // TODO : en fait il suffit de faire un $path = $pathPrefix . $path;
            */

            $path = $options['pathPrefix'] . $path;
        }

        if (
            !empty($options['ext']) &&
            strpos($path, '?') === false &&
            substr($path, -strlen($options['ext'])) !== $options['ext']
        ) {
            $path .= $options['ext'];
        }

        // Check again if path has protocol as `pathPrefix` could be for CDNs.
        if (preg_match('|^([a-z0-9]+:)?//|', $path)) {
            //return $this->routerUrl($path); // TODO : je pense qu'il suffit de retourner le $path plutot que de passer par cette méthode routerUrl() !!!
            return $path;
        }

/*
        if (isset($plugin)) {
            $path = static::inflectString($plugin) . '/' . $path;
        }

        $optionTimestamp = null;
        if (array_key_exists('timestamp', $options)) {
            $optionTimestamp = $options['timestamp'];
        }
*/

        /*
        $webPath = static::assetTimestamp(
            static::webroot($path, $options),
            $optionTimestamp
        );*/

        // TODO : code temporaire !!!!
        //$webPath = '/' . $path;
        $webPath = '' . $path;
        if (strpos($webPath, '//') !== false) {
            $webPath = str_replace('//', '/', $webPath);
        }


        $path = static::encodeUrl($webPath);

        if (!empty($options['fullBase'])) {
            $fullBaseUrl = is_string($options['fullBase'])
                ? $options['fullBase']
                : Router::fullBaseUrl();
            $path = rtrim($fullBaseUrl, '/') . '/' . ltrim($path, '/');
        }

        return $path;
    }

    /**
     * Encodes URL parts using rawurlencode().
     *
     * @param string $url The URL to encode.
     * @return string
     */
    protected static function encodeUrl(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH);
        if ($path === false) {
            $path = $url;
        }

        $parts = array_map('rawurldecode', explode('/', $path));
        $parts = array_map('rawurlencode', $parts);
        $encoded = implode('/', $parts);

        return str_replace($path, $encoded, $url);
    }

}
