<?php

declare(strict_types=1);

namespace Chiron\View\Helper;

use Chiron\ResponseCreator\ResponseCreator;
use Chiron\View\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Chiron\View\StringTemplate;

// TODO : passer les protected en private car la classe est final !!!!
final class HtmlHelper extends Helper
{
    /**
     * List of helpers used by this helper
     *
     * @var array
     */
    protected $helpers = ['Url'];

    /**
     * Names of script & css files that have been included once
     *
     * @var array<string, array>
     */
    protected $_includedAssets = [];

    /**
     * Default config for this class
     *
     * @var array<string, mixed>
     */
    protected $_defaultConfig = [
        'templates' => [
            'meta' => '<meta{{attrs}}/>',
            'metalink' => '<link href="{{url}}"{{attrs}}/>',
            'link' => '<a href="{{url}}"{{attrs}}>{{content}}</a>',
            'mailto' => '<a href="mailto:{{url}}"{{attrs}}>{{content}}</a>',
            'image' => '<img src="{{url}}"{{attrs}}/>',
            'tableheader' => '<th{{attrs}}>{{content}}</th>',
            'tableheaderrow' => '<tr{{attrs}}>{{content}}</tr>',
            'tablecell' => '<td{{attrs}}>{{content}}</td>',
            'tablerow' => '<tr{{attrs}}>{{content}}</tr>',
            'block' => '<div{{attrs}}>{{content}}</div>',
            'blockstart' => '<div{{attrs}}>',
            'blockend' => '</div>',
            'tag' => '<{{tag}}{{attrs}}>{{content}}</{{tag}}>',
            'tagstart' => '<{{tag}}{{attrs}}>',
            'tagend' => '</{{tag}}>',
            'tagselfclosing' => '<{{tag}}{{attrs}}/>',
            'para' => '<p{{attrs}}>{{content}}</p>',
            'parastart' => '<p{{attrs}}>',
            'css' => '<link rel="{{rel}}" href="{{url}}"{{attrs}}/>',
            'style' => '<style{{attrs}}>{{content}}</style>',
            'charset' => '<meta charset="{{charset}}"/>',
            'ul' => '<ul{{attrs}}>{{content}}</ul>',
            'ol' => '<ol{{attrs}}>{{content}}</ol>',
            'li' => '<li{{attrs}}>{{content}}</li>',
            'javascriptblock' => '<script{{attrs}}>{{content}}</script>',
            'javascriptstart' => '<script>',
            'javascriptlink' => '<script src="{{url}}"{{attrs}}></script>',
            'javascriptend' => '</script>',
            'confirmJs' => '{{confirm}}',
        ],
    ];

    /**
     * Creates a link to an external resource and handles basic meta tags
     *
     * Create a meta tag that is output inline:
     *
     * ```
     * $this->Html->meta('icon', 'favicon.ico');
     * ```
     *
     * Append the meta tag to custom view block "meta":
     *
     * ```
     * $this->Html->meta('description', 'A great page', ['block' => true]);
     * ```
     *
     * Append the meta tag to custom view block:
     *
     * ```
     * $this->Html->meta('description', 'A great page', ['block' => 'metaTags']);
     * ```
     *
     * Create a custom meta tag:
     *
     * ```
     * $this->Html->meta(['property' => 'og:site_name', 'content' => 'CakePHP']);
     * ```
     *
     * ### Options
     *
     * - `block` - Set to true to append output to view block "meta" or provide
     *   custom block name.
     *
     * @param array<string, mixed>|string $type The title of the external resource, Or an array of attributes for a
     *   custom meta tag.
     * @param array|string|null $content The address of the external resource or string for content attribute
     * @param array<string, mixed> $options Other attributes for the generated tag. If the type attribute is html,
     *    rss, atom, or icon, the mime-type is returned.
     *
     * @return string|null A completed `<link />` element, or null if the element was sent to a block.
     *
     * @link https://book.cakephp.org/4/en/views/helpers/html.html#creating-meta-tags
     */
    public function meta($type, $content = null, array $options = []): ?string
    {
        if (!is_array($type)) {
            $types = [
                'rss' => ['type' => 'application/rss+xml', 'rel' => 'alternate', 'title' => $type, 'link' => $content],
                'atom' => ['type' => 'application/atom+xml', 'title' => $type, 'link' => $content],
                'icon' => ['type' => 'image/x-icon', 'rel' => 'icon', 'link' => $content],
                'keywords' => ['name' => 'keywords', 'content' => $content],
                'description' => ['name' => 'description', 'content' => $content],
                'robots' => ['name' => 'robots', 'content' => $content],
                'viewport' => ['name' => 'viewport', 'content' => $content],
                'canonical' => ['rel' => 'canonical', 'link' => $content],
                'next' => ['rel' => 'next', 'link' => $content],
                'prev' => ['rel' => 'prev', 'link' => $content],
                'first' => ['rel' => 'first', 'link' => $content],
                'last' => ['rel' => 'last', 'link' => $content],
            ];

            if ($type === 'icon' && $content === null) {
                $types['icon']['link'] = 'favicon.ico';
            }

            if (isset($types[$type])) {
                $type = $types[$type];
            } elseif (!isset($options['type']) && $content !== null) {
                if (is_array($content) && isset($content['_ext'])) {
                    $type = $types[$content['_ext']];
                } else {
                    $type = ['name' => $type, 'content' => $content];
                }
            } elseif (isset($options['type'], $types[$options['type']])) {
                $type = $types[$options['type']];
                unset($options['type']);
            } else {
                $type = [];
            }
        }

        $options += $type + ['block' => null];
        $out = '';

        if (isset($options['link'])) {

            if (is_array($options['link'])) {
                $options['link'] = $this->Url->build($options['link']);
            } else {
                $options['link'] = $this->Url->assetUrl($options['link']);
            }
            if (isset($options['rel']) && $options['rel'] === 'icon') {
                $out = $this->templater()->format('metalink', [
                    'url' => $options['link'],
                    'attrs' => $this->templater()->formatAttributes($options, ['block', 'link']),
                ]);
                $options['rel'] = 'shortcut icon'; // TODO : à virer le shortcut icon n'est plus un tag supporté par html5 c'est pour les vieux navigateurs MS IE
            }
            $out .= $this->templater()->format('metalink', [
                'url' => $options['link'],
                'attrs' => $this->templater()->formatAttributes($options, ['block', 'link']),
            ]);
        } else {
            $out = $this->templater()->format('meta', [
                'attrs' => $this->templater()->formatAttributes($options, ['block', 'type']),
            ]);
        }

        if (empty($options['block'])) {
            return $out;
        }
        if ($options['block'] === true) {
            $options['block'] = __FUNCTION__;
        }
        $this->_View->append($options['block'], $out);

        return null;
    }

    /**
     * Creates an HTML link.
     *
     * If $url starts with "http://" this is treated as an external link. Else,
     * it is treated as a path to controller/action and parsed with the
     * UrlHelper::build() method.
     *
     * If the $url is empty, $title is used instead.
     *
     * ### Options
     *
     * - `escape` Set to false to disable escaping of title and attributes.
     * - `escapeTitle` Set to false to disable escaping of title. Takes precedence
     *   over value of `escape`)
     * - `confirm` JavaScript confirmation message.
     *
     * @param array|string $title The content to be wrapped by `<a>` tags.
     *   Can be an array if $url is null. If $url is null, $title will be used as both the URL and title.
     * @param array|string|null $url Cake-relative URL or array of URL parameters, or
     *   external URL (starts with http://)
     * @param array<string, mixed> $options Array of options and HTML attributes.
     * @return string An `<a />` element.
     * @link https://book.cakephp.org/4/en/views/helpers/html.html#creating-links
     */
    public function link($title, $url = null, array $options = []): string
    {
        $escapeTitle = true;
        if ($url !== null) {
            // TODO : code temporaire, attention cela ne fonctionne pas si on a un lien du style http://xxxx donc il ne faut pas concaténer par défaut le http.basePath !!!! https://github.com/cakephp/cakephp/blob/856741f34393bef25284b86da703e840071c4341/src/Routing/Router.php#L501
            $url = $this->Url->build($url, $options);
            unset($options['fullBase']);
        } else {
            // TODO : code temporaire, attention cela ne fonctionne pas si on a un lien du style http://xxxx donc il ne faut pas concaténer par défaut le http.basePath !!!! https://github.com/cakephp/cakephp/blob/856741f34393bef25284b86da703e840071c4341/src/Routing/Router.php#L501
            $url = $this->Url->build($title);
            $title = htmlspecialchars_decode($url, ENT_QUOTES);
            $title = e(urldecode($title));
            $escapeTitle = false;
        }

        if (isset($options['escapeTitle'])) {
            $escapeTitle = $options['escapeTitle'];
            unset($options['escapeTitle']);
        } elseif (isset($options['escape'])) {
            $escapeTitle = $options['escape'];
        }

        if ($escapeTitle === true) {
            $title = e($title);
        } elseif (is_string($escapeTitle)) {
            /** @psalm-suppress PossiblyInvalidArgument */
            $title = htmlentities($title, ENT_QUOTES, $escapeTitle);
        }

        // TODO : virer la notion de confirm !!!

        $templater = $this->templater();
        $confirmMessage = null;
        if (isset($options['confirm'])) {
            $confirmMessage = $options['confirm'];
            unset($options['confirm']);
        }
        if ($confirmMessage) {
            $confirm = $this->_confirm('return true;', 'return false;');
            $options['data-confirm-message'] = $confirmMessage;
            $options['onclick'] = $templater->format('confirmJs', [
                'confirmMessage' => e($confirmMessage),
                'confirm' => $confirm,
            ]);
        }

        return $templater->format('link', [
            'url' => $url,
            'attrs' => $templater->formatAttributes($options),
            'content' => $title,
        ]);
    }


    /**
     * Creates an HTML link from route path string.
     *
     * ### Options
     *
     * - `escape` Set to false to disable escaping of title and attributes.
     * - `escapeTitle` Set to false to disable escaping of title. Takes precedence
     *   over value of `escape`)
     * - `confirm` JavaScript confirmation message.
     *
     * @param string $title The content to be wrapped by `<a>` tags.
     * @param string $path Cake-relative route path.
     * @param array $params An array specifying any additional parameters.
     *   Can be also any special parameters supported by `Router::url()`.
     * @param array<string, mixed> $options Array of options and HTML attributes.
     * @return string An `<a />` element.
     * @see \Cake\Routing\Router::pathUrl()
     * @link https://book.cakephp.org/4/en/views/helpers/html.html#creating-links
     */
    /*
    public function linkFromPath(string $title, string $path, array $params = [], array $options = []): string
    {
        return $this->link($title, ['_path' => $path] + $params, $options);
    }*/


    /**
     * Creates a link element for CSS stylesheets.
     *
     * ### Usage
     *
     * Include one CSS file:
     *
     * ```
     * echo $this->Html->css('styles.css');
     * ```
     *
     * Include multiple CSS files:
     *
     * ```
     * echo $this->Html->css(['one.css', 'two.css']);
     * ```
     *
     * Add the stylesheet to view block "css":
     *
     * ```
     * $this->Html->css('styles.css', ['block' => true]);
     * ```
     *
     * Add the stylesheet to a custom block:
     *
     * ```
     * $this->Html->css('styles.css', ['block' => 'layoutCss']);
     * ```
     *
     * ### Options
     *
     * - `block` Set to true to append output to view block "css" or provide
     *   custom block name.
     * - `once` Whether the css file should be checked for uniqueness. If true css
     *   files  will only be included once, use false to allow the same
     *   css to be included more than once per request.
     * - `plugin` False value will prevent parsing path as a plugin
     * - `rel` Defaults to 'stylesheet'. If equal to 'import' the stylesheet will be imported.
     * - `fullBase` If true the URL will get a full address for the css file.
     *
     * All other options will be treated as HTML attributes. If the request contains a
     * `cspStyleNonce` attribute, that value will be applied as the `nonce` attribute on the
     * generated HTML.
     *
     * @param array<string>|string $path The name of a CSS style sheet or an array containing names of
     *   CSS stylesheets. If `$path` is prefixed with '/', the path will be relative to the webroot
     *   of your application. Otherwise, the path will be relative to your CSS path, usually webroot/css.
     * @param array<string, mixed> $options Array of options and HTML arguments.
     * @return string|null CSS `<link />` or `<style />` tag, depending on the type of link.
     * @link https://book.cakephp.org/4/en/views/helpers/html.html#linking-to-css-files
     */
    public function css($path, array $options = []): ?string
    {
        $options += [
            'once' => true,
            'block' => null,
            'rel' => 'stylesheet',
            //'nonce' => $this->_View->getRequest()->getAttribute('cspStyleNonce'),
        ];

        if (is_array($path)) {
            $out = '';
            foreach ($path as $i) {
                $out .= "\n\t" . (string)$this->css($i, $options);
            }
            if (empty($options['block'])) {
                return $out . "\n";
            }

            return null;
        }

        $url = $this->Url->assetUrl($path, $options);
        $options = array_diff_key($options, ['fullBase' => null, 'pathPrefix' => null]);

        if ($options['once'] && isset($this->_includedAssets[__METHOD__][$path])) {
            return null;
        }
        unset($options['once']);
        $this->_includedAssets[__METHOD__][$path] = true;

        $templater = $this->templater();
        if ($options['rel'] === 'import') {
            $out = $templater->format('style', [
                'attrs' => $templater->formatAttributes($options, ['rel', 'block']),
                'content' => '@import url(' . $url . ');',
            ]);
        } else {
            $out = $templater->format('css', [
                'rel' => $options['rel'],
                'url' => $url,
                'attrs' => $templater->formatAttributes($options, ['rel', 'block']),
            ]);
        }

        if (empty($options['block'])) {
            return $out;
        }
        if ($options['block'] === true) {
            $options['block'] = __FUNCTION__;
        }
        $this->_View->append($options['block'], $out);

        return null;
    }

    /**
     * Returns one or many `<script>` tags depending on the number of scripts given.
     *
     * If the filename is prefixed with "/", the path will be relative to the base path of your
     * application. Otherwise, the path will be relative to your JavaScript path, usually webroot/js.
     *
     * ### Usage
     *
     * Include one script file:
     *
     * ```
     * echo $this->Html->script('styles.js');
     * ```
     *
     * Include multiple script files:
     *
     * ```
     * echo $this->Html->script(['one.js', 'two.js']);
     * ```
     *
     * Add the script file to a custom block:
     *
     * ```
     * $this->Html->script('styles.js', ['block' => 'bodyScript']);
     * ```
     *
     * ### Options
     *
     * - `block` Set to true to append output to view block "script" or provide
     *   custom block name.
     * - `once` Whether the script should be checked for uniqueness. If true scripts will only be
     *   included once, use false to allow the same script to be included more than once per request.
     * - `plugin` False value will prevent parsing path as a plugin
     * - `fullBase` If true the url will get a full address for the script file.
     *
     * All other options will be added as attributes to the generated script tag.
     * If the current request has a `cspScriptNonce` attribute, that value will
     * be inserted as a `nonce` attribute on the script tag.
     *
     * @param array<string>|string $url String or array of javascript files to include
     * @param array<string, mixed> $options Array of options, and html attributes see above.
     * @return string|null String of `<script />` tags or null if block is specified in options
     *   or if $once is true and the file has been included before.
     * @link https://book.cakephp.org/4/en/views/helpers/html.html#linking-to-javascript-files
     */
    public function script($url, array $options = []): ?string
    {
        $defaults = [
            'block' => null,
            'once' => true,
            //'nonce' => $this->_View->getRequest()->getAttribute('cspScriptNonce'),
        ];
        $options += $defaults;

        if (is_array($url)) {
            $out = '';
            foreach ($url as $i) {
                $out .= "\n\t" . (string)$this->script($i, $options);
            }
            if (empty($options['block'])) {
                return $out . "\n";
            }

            return null;
        }

        $url = $this->Url->assetUrl($url, $options);
        $options = array_diff_key($options, ['fullBase' => null, 'pathPrefix' => null]);

        if ($options['once'] && isset($this->_includedAssets[__METHOD__][$url])) {
            return null;
        }
        $this->_includedAssets[__METHOD__][$url] = true;

        $out = $this->templater()->format('javascriptlink', [
            'url' => $url,
            'attrs' => $this->templater()->formatAttributes($options, ['block', 'once']),
        ]);

        if (empty($options['block'])) {
            return $out;
        }
        if ($options['block'] === true) {
            $options['block'] = __FUNCTION__;
        }
        $this->_View->append($options['block'], $out);

        return null;
    }

    /**
     * Creates a formatted IMG element.
     *
     * This method will set an empty alt attribute if one is not supplied.
     *
     * ### Usage:
     *
     * Create a regular image:
     *
     * ```
     * echo $this->Html->image('cake_icon.png', ['alt' => 'CakePHP']);
     * ```
     *
     * Create an image link:
     *
     * ```
     * echo $this->Html->image('cake_icon.png', ['alt' => 'CakePHP', 'url' => 'https://cakephp.org']);
     * ```
     *
     * ### Options:
     *
     * - `url` If provided an image link will be generated and the link will point at
     *   `$options['url']`.
     * - `fullBase` If true the src attribute will get a full address for the image file.
     * - `plugin` False value will prevent parsing path as a plugin
     *
     * @param array|string $path Path to the image file, relative to the webroot/img/ directory.
     * @param array<string, mixed> $options Array of HTML attributes. See above for special options.
     * @return string completed img tag
     * @link https://book.cakephp.org/4/en/views/helpers/html.html#linking-to-images
     */
    public function image($path, array $options = []): string
    {
        if (is_string($path)) {
            $path = $this->Url->assetUrl($path, $options);
        } else {
            $path = $this->Url->build($path, $options);
        }
        $options = array_diff_key($options, ['fullBase' => null, 'pathPrefix' => null]);

        if (!isset($options['alt'])) {
            $options['alt'] = '';
        }

        $url = false;
        if (!empty($options['url'])) {
            $url = $options['url'];
            unset($options['url']);
        }

        $templater = $this->templater();
        $image = $templater->format('image', [
            'url' => $path,
            'attrs' => $templater->formatAttributes($options),
        ]);

        if ($url) {
            return $templater->format('link', [
                'url' => $this->Url->build($url),
                'attrs' => null,
                'content' => $image,
            ]);
        }

        return $image;
    }

    /**
     * Returns an audio/video element
     *
     * ### Usage
     *
     * Using an audio file:
     *
     * ```
     * echo $this->Html->media('audio.mp3', ['fullBase' => true]);
     * ```
     *
     * Outputs:
     *
     * ```
     * <video src="http://www.somehost.com/files/audio.mp3">Fallback text</video>
     * ```
     *
     * Using a video file:
     *
     * ```
     * echo $this->Html->media('video.mp4', ['text' => 'Fallback text']);
     * ```
     *
     * Outputs:
     *
     * ```
     * <video src="/files/video.mp4">Fallback text</video>
     * ```
     *
     * Using multiple video files:
     *
     * ```
     * echo $this->Html->media(
     *      ['video.mp4', ['src' => 'video.ogv', 'type' => "video/ogg; codecs='theora, vorbis'"]],
     *      ['tag' => 'video', 'autoplay']
     * );
     * ```
     *
     * Outputs:
     *
     * ```
     * <video autoplay="autoplay">
     *      <source src="/files/video.mp4" type="video/mp4"/>
     *      <source src="/files/video.ogv" type="video/ogv; codecs='theora, vorbis'"/>
     * </video>
     * ```
     *
     * ### Options
     *
     * - `tag` Type of media element to generate, either "audio" or "video".
     *  If tag is not provided it's guessed based on file's mime type.
     * - `text` Text to include inside the audio/video tag
     * - `pathPrefix` Path prefix to use for relative URLs, defaults to 'files/'
     * - `fullBase` If provided the src attribute will get a full address including domain name
     *
     * @param array|string $path Path to the video file, relative to the webroot/{$options['pathPrefix']} directory.
     *  Or an array where each item itself can be a path string or an associate array containing keys `src` and `type`
     * @param array<string, mixed> $options Array of HTML attributes, and special options above.
     * @return string Generated media element
     */
    public function media($path, array $options = []): string
    {
        $options += [
            'tag' => null,
            'pathPrefix' => 'files/',
            'text' => '',
        ];

        if (!empty($options['tag'])) {
            $tag = $options['tag'];
        } else {
            $tag = null;
        }

        if (is_array($path)) {
            $sourceTags = '';
            foreach ($path as &$source) {
                if (is_string($source)) {
                    $source = [
                        'src' => $source,
                    ];
                }
                if (!isset($source['type'])) {
                    $ext = pathinfo($source['src'], PATHINFO_EXTENSION);
                    $source['type'] = $this->getMimeType($ext);
                }
                $source['src'] = $this->Url->assetUrl($source['src'], $options);
                $sourceTags .= $this->templater()->format('tagselfclosing', [
                    'tag' => 'source',
                    'attrs' => $this->templater()->formatAttributes($source),
                ]);
            }
            unset($source);
            $options['text'] = $sourceTags . $options['text'];
            unset($options['fullBase']);
        } else {
            if (empty($path) && !empty($options['src'])) {
                $path = $options['src'];
            }
            $options['src'] = $this->Url->assetUrl($path, $options);
        }

        if ($tag === null) {
            if (is_array($path)) {
                $mimeType = $path[0]['type'];
            } else {
                /** @var string $mimeType */
                $mimeType = $this->getMimeType(pathinfo($path, PATHINFO_EXTENSION));
            }

            // TODO : attention il se passe quoi si on retourne un tableau de mime (par exemple pour l'extension 'xhtml')
            // TODO : attention il y a une erreur dans le code d'origine de cakephp car si le mimetype n'est pas trouvée c'est false qui est stocké dans le mimeType, et lorsqu'on va utiliser la fonction preg_match qui attend un string pour la comparaison cela va péter !!!!
            if (preg_match('#^video/#', $mimeType)) {
                $tag = 'video';
            } else {
                $tag = 'audio';
            }
        }

        if (isset($options['poster'])) {
            $options['poster'] = $this->Url->assetUrl(
                $options['poster'],
                //['pathPrefix' => Configure::read('App.imageBaseUrl')] + $options
                ['pathPrefix' => ''] + $options
            );
        }
        $text = $options['text'];

        $options = array_diff_key($options, [
            'tag' => null,
            'fullBase' => null,
            'pathPrefix' => null,
            'text' => null,
        ]);

        return $this->tag($tag, $text, $options);
    }

    /**
     * Returns the mime type definition for an alias
     *
     * e.g `getMimeType('pdf'); // returns 'application/pdf'`
     *
     * @param string $alias the content type alias to map
     * @return array|string|false String mapped mime type or false if $alias is not mapped
     */
    // https://github.com/cakephp/cakephp/blob/32e3c532fea8abe2db8b697f07dfddf4dfc134ca/src/Http/Response.php#L134
    //https://github.com/symfony/mime/blob/6.1/MimeTypes.php#L1820
    //https://github.com/yiisoft/yii2/blob/master/framework/helpers/mimeTypes.php
    protected function getMimeType(string $alias)
    {
        // TODO : ajouter la liste de tous les mimes !!!! pour l'instant c'est juste un exemple !!!!
        $mimeTypes = [
            'html' => ['text/html', '*/*'],
            'webp' => 'image/webp',
            'ogv' => 'video/ogg',
            'webm' => 'video/webm',
            'mp4' => 'video/mp4',
            'mp3' => 'audio/mpeg',
            'json' => 'application/json',
            'xml' => ['application/xml', 'text/xml'],
            'xhtml' => ['application/xhtml+xml', 'application/xhtml', 'text/xhtml'],
            'webp' => 'image/webp',
            'rss' => 'application/rss+xml',
            'ai' => 'application/postscript',
            'bcpio' => 'application/x-bcpio'
        ];

        return $mimeTypes[$alias] ?? false; // TODO : forcer le $alias en strtolower et renommer ce paramétre en $extension, et retourner un tableau vide si on n'a pas trouvé le mime. Il faudra aussi mettre le return type de la méthode à "array" et pour chaque valeur il faut que ce soit un tableau même d'un seul élément string !!!

        //return $mimeTypes[strtolower($alias)] ?? [];
    }

     /**
     * Returns a formatted DIV tag for HTML FORMs.
     *
     * ### Options
     *
     * - `escape` Whether the contents should be html_entity escaped.
     *
     * @param string|null $class CSS class name of the div element.
     * @param string|null $text String content that will appear inside the div element.
     *   If null, only a start tag will be printed
     * @param array<string, mixed> $options Additional HTML attributes of the DIV tag
     * @return string The formatted DIV element
     */
    public function div(?string $class = null, ?string $text = null, array $options = []): string
    {
        if (!empty($class)) {
            $options['class'] = $class;
        }

        return $this->tag('div', $text, $options);
    }

    /**
     * Returns a formatted block tag, i.e DIV, SPAN, P.
     *
     * ### Options
     *
     * - `escape` Whether the contents should be html_entity escaped.
     *
     * @param string $name Tag name.
     * @param string|null $text String content that will appear inside the div element.
     *   If null, only a start tag will be printed
     * @param array<string, mixed> $options Additional HTML attributes of the DIV tag, see above.
     * @return string The formatted tag element
     */
    //The tag builder respects HTML5 void elements (https://www.w3.org/TR/html5/syntax.html#void-elements) if no content is passed, and omits closing tags for those elements.
    // https://api.rubyonrails.org/v5.2.0/classes/ActionView/Helpers/TagHelper.html
    public function tag(string $name, ?string $text = null, array $options = []): string
    {
        if (isset($options['escape']) && $options['escape']) {
            $text = e($text);
            unset($options['escape']);
        }
        if ($text === null) {
            $tag = 'tagstart';
        } else {
            $tag = 'tag';
        }

        return $this->templater()->format($tag, [
            'attrs' => $this->templater()->formatAttributes($options),
            'tag' => $name,
            'content' => $text,
        ]);
    }

    /**
     * Returns a formatted P tag.
     *
     * ### Options
     *
     * - `escape` Whether the contents should be html_entity escaped.
     *
     * @param string|null $class CSS class name of the p element.
     * @param string|null $text String content that will appear inside the p element.
     * @param array<string, mixed> $options Additional HTML attributes of the P tag
     * @return string The formatted P element
     */
    // TODO : renommer la méthode en paragraph() ???
    public function para(?string $class, ?string $text, array $options = []): string
    {
        if (!empty($options['escape'])) {
            $text = e($text);
        }
        if ($class) {
            $options['class'] = $class;
        }
        $tag = 'para';
        if ($text === null) {
            $tag = 'parastart';
        }

        return $this->templater()->format($tag, [
            'attrs' => $this->templater()->formatAttributes($options),
            'content' => $text,
        ]);
    }

    /**
     * Build a nested list (UL/OL) out of an associative array.
     *
     * Options for $options:
     *
     * - `tag` - Type of list tag to use (ol/ul)
     *
     * Options for $itemOptions:
     *
     * - `even` - Class to use for even rows.
     * - `odd` - Class to use for odd rows.
     *
     * @param array $list Set of elements to list
     * @param array<string, mixed> $options Options and additional HTML attributes of the list (ol/ul) tag.
     * @param array<string, mixed> $itemOptions Options and additional HTML attributes of the list item (LI) tag.
     * @return string The nested list
     * @link https://book.cakephp.org/4/en/views/helpers/html.html#creating-nested-lists
     */
    public function nestedList(array $list, array $options = [], array $itemOptions = []): string
    {
        $options += ['tag' => 'ul'];
        $items = $this->_nestedListItem($list, $options, $itemOptions);

        return $this->templater()->format($options['tag'], [
            'attrs' => $this->templater()->formatAttributes($options, ['tag']),
            'content' => $items,
        ]);
    }

    /**
     * Internal function to build a nested list (UL/OL) out of an associative array.
     *
     * @param array $items Set of elements to list.
     * @param array<string, mixed> $options Additional HTML attributes of the list (ol/ul) tag.
     * @param array<string, mixed> $itemOptions Options and additional HTML attributes of the list item (LI) tag.
     * @return string The nested list element
     * @see \Cake\View\Helper\HtmlHelper::nestedList()
     */
    protected function _nestedListItem(array $items, array $options, array $itemOptions): string
    {
        $out = '';

        $index = 1;
        foreach ($items as $key => $item) {
            if (is_array($item)) {
                $item = $key . $this->nestedList($item, $options, $itemOptions);
            }
            if (isset($itemOptions['even']) && $index % 2 === 0) {
                $itemOptions['class'] = $itemOptions['even'];
            } elseif (isset($itemOptions['odd']) && $index % 2 !== 0) {
                $itemOptions['class'] = $itemOptions['odd'];
            }
            $out .= $this->templater()->format('li', [
                'attrs' => $this->templater()->formatAttributes($itemOptions, ['even', 'odd']),
                'content' => $item,
            ]);
            $index++;
        }

        return $out;
    }






































    /**
     * Builds CSS style data from an array of CSS properties
     *
     * ### Usage:
     *
     * ```
     * echo $this->Html->style(['margin' => '10px', 'padding' => '10px'], true);
     *
     * // creates
     * 'margin:10px;padding:10px;'
     * ```
     *
     * @param array<string, string> $data Style data array, keys will be used as property names, values as property values.
     * @param bool $oneLine Whether the style block should be displayed on one line.
     * @return string CSS styling data
     * @link https://book.cakephp.org/4/en/views/helpers/html.html#creating-css-programatically
     */
    public function style(array $data, bool $oneLine = true): string
    {
        $out = [];
        foreach ($data as $key => $value) {
            $out[] = $key . ':' . $value . ';';
        }
        if ($oneLine) {
            return implode(' ', $out);
        }

        return implode("\n", $out);
    }


    /**
     * Returns a charset META-tag.
     *
     * @param string|null $charset The character set to be used in the meta tag. If empty,
     *  The App.encoding value will be used. Example: "utf-8".
     * @return string A meta tag containing the specified character set.
     * @link https://book.cakephp.org/4/en/views/helpers/html.html#creating-charset-tags
     */
    public function charset(?string $charset = null): string
    {
        // TODO : faire un test en passant une chaine vide !!!! car il faut aussi gérer ce cas !!! et virer les 2 utilisation en empty() !!!
        if (empty($charset)) {
            $charset = strtolower((string)config('http')->get('default_charset'));
        }

        return $this->templater()->format('charset', [
            'charset' => !empty($charset) ? $charset : 'utf-8',
        ]);
    }





















    /**
     * Returns the templater instance.
     *
     * @return \Cake\View\StringTemplate
     */
    // TODO : à mettre dans un trait car ca sera utilisé par plusieurs Helpers !!!!
    public function templater(): StringTemplate
    {
        return new StringTemplate($this->_defaultConfig['templates']);
    }


/*
    public function templater_SAVE(): StringTemplate
    {
        $templater = new StringTemplate();

        $templates = $this->_defaultConfig['templates']; //$this->getConfig('templates');
        if ($templates) {
            if (is_string($templates)) {
                // TODO : Ces 2 lignes sont à virer car on va pas charger le template depuis un fichier, mais on utilise directement un tableau !!!
                $templater->add($this->_defaultConfig['templates']);
                $templater->load($templates);
            } else {
                $templater->add($templates);
            }
        }

        return $templater;
    }
*/
}
