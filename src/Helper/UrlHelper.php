<?php

declare(strict_types=1);

namespace Chiron\View\Helper;

// TODO : créer une méthode (eventuellement la mettre dans la classe Uri du package chiron/http et déplacer cette classe dans le package chiron/http-utils) pour déterminer si l'url est absolute : '/^((https?:)?\/\/|data:)/i'     https://github.com/fisharebest/laravel-assets/blob/main/src/Assets.php#L548
// TODO : je pense qu'on peut améliorer la regex en évitant d'utiliser le séparateur "/" je pense qu'une expression comme ca doit fonctionner : '#^((https?:)?//|data:)#i'

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
     *
     * @return string Full translated URL with base path.
     */
    // TODO : code temporaire, attention cela ne fonctionne pas si on a un lien du style http://xxxx donc il ne faut pas concaténer par défaut le http.basePath !!!! https://github.com/cakephp/cakephp/blob/856741f34393bef25284b86da703e840071c4341/src/Routing/Router.php#L501

    // TODO : Mettre les 2 options sous forme de paramétre de méthode build(string $url, bool $fullBase = false, bool $escape = true) comme ca ca force le cast en booleen sur les valeurs manipulées, et depuis php8 on pourra appeller la méthode comme ca : $helper->build('/post/id', escape: true) ou $helper->build('/post/id', fullBase: true)
    public function build(string $url, array $options = []): string
    {
        $defaults = [
            'fullBase' => false,
            'escape'   => true,
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
     *   elements or query string parameters. If string it can be any valid url
     *   string or it can be an UriInterface instance.
     * @param bool $full If true, the full base URL will be prepended to the result.
     *   Default is false.
     *
     * @return string Full translated URL with base path.
     *
     * @throws \Cake\Core\Exception\CakeException When the route name is not found
     */
    // TODO : code temporaire, attention cela ne fonctionne pas si on a un lien du style http://xxxx donc il ne faut pas concaténer par défaut le http.basePath !!!!
    //https://github.com/cakephp/cakephp/blob/856741f34393bef25284b86da703e840071c4341/src/Routing/Router.php#L501

    // TODO : corriger le phpdoc pour le paramétre $url !!!
    private function buildUrl(string $url, bool $full = false): string
    {
        // TODO : pourquoi on test pas aussi 'data:' ??? ca devrait aussi être une plainString et donc on ne la modifie pas !!!! https://developer.mozilla.org/fr/docs/Web/HTTP/Basics_of_HTTP/Data_URLs
        // TODO : dans quel cas on peut avoir une url qui commence par un "#" ou un "?" je suppose que c'est un morceau d'url et qu'on manipule le segment au lieu de manipuler l'url ???
        // TODO : externaliser ce controle dans une méthode private de la classe car on peut le mutualiser avec un controle nécessaire dans la méthode buildAsset
        // TODO : le controle sur "#" et '?' ne semble pas utile !!!! virer ces 2 strpos !!!
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
            $output = str_replace('//', '/', '/' . $output); // TODO : ca peut arriver ce cas là ???
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
     * @param string               $path    Path string.
     * @param array<string, mixed> $options Options array. Possible keys:
     *   `fullBase` Return full URL with domain name
     *   `pathPrefix` Path prefix for relative URLs
     *   `plugin` False value will prevent parsing path as a plugin
     *   `timestamp` Overrides the value of `Asset.timestamp` in Configure.
     *        Set to false to skip timestamp generation.
     *        Set to true to apply timestamps when debug is true. Set to 'force' to always
     *        enable timestamping regardless of debug value.
     *
     * @return string Generated URL
     */
    public function image(string $path, array $options = []): string
    {
        $pathPrefix = 'assets/img/'; //Configure::read('App.imageBaseUrl');

        return $this->assetUrl($path, $options + compact('pathPrefix'));
    }

    /**
     * Generates URL for given CSS file.
     *
     * Depending on options passed provides full URL with domain name. Also calls
     * `Helper::assetTimestamp()` to add timestamp to local files.
     *
     * @param string               $path    Path string.
     * @param array<string, mixed> $options Options array. Possible keys:
     *   `fullBase` Return full URL with domain name
     *   `pathPrefix` Path prefix for relative URLs
     *   `ext` Asset extension to append
     *   `plugin` False value will prevent parsing path as a plugin
     *   `timestamp` Overrides the value of `Asset.timestamp` in Configure.
     *        Set to false to skip timestamp generation.
     *        Set to true to apply timestamps when debug is true. Set to 'force' to always
     *        enable timestamping regardless of debug value.
     *
     * @return string Generated URL
     */
    public function css(string $path, array $options = []): string
    {
        $pathPrefix = 'assets/css/'; //Configure::read('App.cssBaseUrl');
        $ext = '.css';

        return $this->assetUrl($path, $options + compact('pathPrefix', 'ext'));
    }

    /**
     * Generates URL for given javascript file.
     *
     * Depending on options passed provides full URL with domain name. Also calls
     * `Helper::assetTimestamp()` to add timestamp to local files.
     *
     * @param string               $path    Path string.
     * @param array<string, mixed> $options Options array. Possible keys:
     *   `fullBase` Return full URL with domain name
     *   `pathPrefix` Path prefix for relative URLs
     *   `ext` Asset extension to append
     *   `plugin` False value will prevent parsing path as a plugin
     *   `timestamp` Overrides the value of `Asset.timestamp` in Configure.
     *        Set to false to skip timestamp generation.
     *        Set to true to apply timestamps when debug is true. Set to 'force' to always
     *        enable timestamping regardless of debug value.
     *
     * @return string Generated URL
     */
    public function script(string $path, array $options = []): string
    {
        $pathPrefix = 'assets/js/'; //Configure::read('App.jsBaseUrl');
        $ext = '.js';

        return $this->assetUrl($path, $options + compact('pathPrefix', 'ext'));
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
     *
     * @return string Generated URL
     */
    // TODO : virer la notion de plugin
    public function assetUrl(string $path, array $options = []): string
    {
        return e($this->assetUrlInternal($path, $options));
    }

    private function assetUrlInternal(string $path, array $options = []): string
    {
        // TODO : conserver ce test ? car visiblement le if juste en dessous va aussi vérifier si il y a un début d'url avec la chaine "xxx:" comme par exemple "mailto:" donc ce controle est redondant avec celui juste en dessous !!!
        // TODO : attention ce controle ne fonctionnera pas dans le cas ou on utilise "data:,Hello%2C%20World!" cad sans préciser le mimetype qui contient un slash "data:text/plain;base64,SGVsbG8sIFdvcmxkIQ%3D%3D" cf https://developer.mozilla.org/fr/docs/Web/HTTP/Basics_of_HTTP/Data_URLs

        // TODO : externaliser le controle présent dans le buildUrl et réutiliser ce controle ici ???
        if (preg_match('/^data:[a-z]+\/[a-z]+;/', $path)) {
            return $path;
        }

        // TODO : vérifier si strpos('//') === 0 pour gérer le cas du "Protocol-relative URL" qui n'ont pas de scheme !!!!
        // TODO : utiliser str_contains et str_start_with =>     if (str_contains($path, '://') || str_starts_with($path, '//')) {
        if (strpos($path, '://') !== false || preg_match('/^[a-z]+:/i', $path)) {
            return $path;
        }

        if (! empty($options['pathPrefix']) && $path[0] !== '/') {
            $path = $options['pathPrefix'] . $path;
        }

        if (
            ! empty($options['ext']) &&
            strpos($path, '?') === false &&
            substr($path, -strlen($options['ext'])) !== $options['ext']
        ) {
            $path .= $options['ext'];
        }

        // TODO : attention on va avoir un probléme si on utilise un $path du genre '//toto.com' car si le pathPrefix ne va pas etre ajouté, l'extension elle sera ajoutée et donc on aura un truc du genre "//toto.com.css" par exemple. Je pense que lors de la vérification du strpos('://') il faut aussi vérifier le strpos('//') === 0

        // Check again if path has protocol as `pathPrefix` could be for CDNs.
        if (preg_match('|^([a-z0-9]+:)?//|', $path)) {
            return $path;
        }

        $optionTimestamp = null;
        if (array_key_exists('timestamp', $options)) {
            $optionTimestamp = $options['timestamp'];
        }

        $webPath = config('http')->get('base_path') . $path;
        // TODO : c'est pas trés propre comme code, il faudrait s'assurer que le base_path se termine par un slash et stripper sur le coté gauche le '/' dans le $path ???
        // TODO : faire un str_contains au lieu de strpos
        if (strpos($webPath, '//') !== false) {
            return str_replace('//', '/', $webPath);
        }

        $webPath = static::assetTimestamp($webPath, $optionTimestamp); // TODO : ne pas utiliser une méthode statique !!!

        $path = static::encodeUrl($webPath); // TODO : ne pas utiliser une méthode statique !!!

        if (! empty($options['fullBase'])) {
            $fullBaseUrl = is_string($options['fullBase'])
                ? $options['fullBase']
                : Router::fullBaseUrl();
            $path = rtrim($fullBaseUrl, '/') . '/' . ltrim($path, '/');
        }

        return $path;
    }

    /**
     * Adds a timestamp to a file based resource based on the value of `Asset.timestamp` in
     * Configure. If Asset.timestamp is true and debug is true, or Asset.timestamp === 'force'
     * a timestamp will be added.
     *
     * @param string $path The file path to timestamp, the path must be inside `App.wwwRoot` in Configure.
     * @param string|bool|null $timestamp If set will overrule the value of `Asset.timestamp` in Configure.
     * @return string Path with a timestamp added, or not.
     */
    // TODO : passer la méthode en private ???
    public static function assetTimestamp(string $path, string|bool|null $timestamp = null): string
    {
        // Faire un str_contains !!!!
        if (strpos($path, '?') !== false) {
            return $path;
        }

        if ($timestamp === null) {
            //$timestamp = Configure::read('Asset.timestamp');
            $timestamp = 'force'; // TODO : test temporaire !!! à virer prochainement !!!
        }
        //$timestampEnabled = $timestamp === 'force' || ($timestamp === true && Configure::read('debug'));
        $timestampEnabled = $timestamp === 'force'; // TODO : test temporaire !!! à virer prochainement !!!
        if ($timestampEnabled) {

            /*
            $filepath = preg_replace(
                '/^' . preg_quote(static::requestWebroot(), '/') . '/',
                '',
                urldecode($path)
            );*/



            // TODO : je pense qu'il faut récupérer le http.base_path [config('http')->get('base_path')] et éventuellement si il est vide mettre un '/' par défault !!!!
            $basePath = config('http')->get('base_path');
            $filepath = preg_replace(
                '/^' . preg_quote($basePath, '/') . '/',
                '',
                urldecode($path)
            );

            //$webrootPath = Configure::read('App.wwwRoot') . str_replace('/', DIRECTORY_SEPARATOR, $filepath);
            $webrootPath = directory('@public') . $filepath;



            if (is_file($webrootPath)) {
                return $path . '?' . filemtime($webrootPath);
            }
        }

        return $path;
    }

    /**
     * Encodes URL parts using rawurlencode().
     *
     * @param string $url The URL to encode.
     *
     * @return string
     */
    // TODO : ne pas utiliser une méthode statique + passer la méthode en private car la classe est en mode "final" !!!!
    // TODO : déplacer cette méthode dans une classe Uri::class du package chiron/http-utils ???
    protected static function encodeUrl(string $url): string
    {
        // TODO : exemple qui retourne un false lors du parse_url
        /*
            parse_url("http:///example.com");
            parse_url("http://:80");
            parse_url("http://user@:80");
        */
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
