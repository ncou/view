<?php

declare(strict_types=1);

namespace Chiron\View\Tests\Helper;

use Chiron\View\TemplatePath;
use PHPUnit\Framework\TestCase;
use Chiron\View\StringTemplate;
use Chiron\View\Helper\HtmlHelper;

//https://github.com/cakephp/cakephp/blob/32e3c532fea8abe2db8b697f07dfddf4dfc134ca/tests/TestCase/View/Helper/HtmlHelperTest.php

class HtmlHelperTest extends AbstractHelperTestCase
{
    /**
     * Helper to be tested
     *
     * @var \Chiron\View\Helper\HtmlHelper
     */
    protected $Html;

    /**
     * setUp method
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->Html = new HtmlHelper();
    }

    /**
     * Test generating favicon's with meta()
     */
    public function testMetaIcon(): void
    {
        $result = $this->Html->meta('icon', 'favicon.ico');
        $expected = [
            'link' => ['href' => 'preg:/.*favicon\.ico/', 'type' => 'image/x-icon', 'rel' => 'icon'],
            ['link' => ['href' => 'preg:/.*favicon\.ico/', 'type' => 'image/x-icon', 'rel' => 'shortcut icon']],
        ];
        $this->assertHtml($expected, $result);

        $result = $this->Html->meta('icon');
        $expected = [
            'link' => ['href' => 'preg:/.*favicon\.ico/', 'type' => 'image/x-icon', 'rel' => 'icon'],
            ['link' => ['href' => 'preg:/.*favicon\.ico/', 'type' => 'image/x-icon', 'rel' => 'shortcut icon']],
        ];
        $this->assertHtml($expected, $result);

/*
        $result = $this->Html->meta('icon', '/favicon.png?one=two&three=four');
        $url = '/favicon.png?one=two&amp;three=four';
        $expected = [
            'link' => [
                'href' => $url,
                'type' => 'image/x-icon',
                'rel' => 'icon',
            ],
            [
                'link' => [
                    'href' => $url,
                    'type' => 'image/x-icon',
                    'rel' => 'shortcut icon',
                ],
            ],
        ];
        $this->assertHtml($expected, $result);

        $result = $this->Html->meta('icon', 'x:"><script>alert(1)</script>');
        $url = 'x:&quot;&gt;&lt;script&gt;alert(1)&lt;/script&gt;';
        $expected = [
            'link' => [
                'href' => $url,
                'type' => 'image/x-icon',
                'rel' => 'icon',
            ],
            [
                'link' => [
                    'href' => $url,
                    'type' => 'image/x-icon',
                    'rel' => 'shortcut icon',
                ],
            ],
        ];
        $this->assertHtml($expected, $result);
*/

/*
        $request = Router::getRequest()->withAttribute('webroot', '/testing/');
        Router::setRequest($request);
        $result = $this->Html->meta('icon');
        $expected = [
            'link' => ['href' => '/testing/favicon.ico', 'type' => 'image/x-icon', 'rel' => 'icon'],
            ['link' => ['href' => '/testing/favicon.ico', 'type' => 'image/x-icon', 'rel' => 'shortcut icon']],
        ];
        $this->assertHtml($expected, $result);
        */
    }

    /**
     * testMeta method
     */
    public function testMeta(): void
    {
        //Router::createRouteBuilder('/')->connect('/:controller', ['action' => 'index']);

/*
        $result = $this->Html->meta('this is an rss feed', ['controller' => 'Posts', '_ext' => 'rss']);
        $expected = ['link' => ['href' => 'preg:/.*\/posts\.rss/', 'type' => 'application/rss+xml', 'rel' => 'alternate', 'title' => 'this is an rss feed']];
        $this->assertHtml($expected, $result);

        $result = $this->Html->meta('rss', ['controller' => 'Posts', '_ext' => 'rss'], ['title' => 'this is an rss feed']);
        $expected = ['link' => ['href' => 'preg:/.*\/posts\.rss/', 'type' => 'application/rss+xml', 'rel' => 'alternate', 'title' => 'this is an rss feed']];
        $this->assertHtml($expected, $result);

        $result = $this->Html->meta('atom', ['controller' => 'Posts', '_ext' => 'xml']);
        $expected = ['link' => ['href' => 'preg:/.*\/posts\.xml/', 'type' => 'application/atom+xml', 'title' => 'atom']];
        $this->assertHtml($expected, $result);

        $result = $this->Html->meta('atom', ['controller' => 'Posts', '_ext' => 'xml'], ['link' => '/articles.rss']);
        $expected = ['link' => ['href' => 'preg:/.*\/articles\.rss/', 'type' => 'application/atom+xml', 'title' => 'atom']];
        $this->assertHtml($expected, $result);
*/


        $result = $this->Html->meta('nonexistent');
        $expected = ['<meta'];
        $this->assertHtml($expected, $result);

        $result = $this->Html->meta('nonexistent', 'some content');
        $expected = ['meta' => ['name' => 'nonexistent', 'content' => 'some content']];
        $this->assertHtml($expected, $result);

        $result = $this->Html->meta('nonexistent', '/posts.xpp', ['type' => 'atom']);
        $expected = ['link' => ['href' => 'preg:/.*\/posts\.xpp/', 'type' => 'application/atom+xml', 'title' => 'nonexistent']];
        $this->assertHtml($expected, $result);

        $result = $this->Html->meta('keywords', 'these, are, some, meta, keywords');
        $expected = ['meta' => ['name' => 'keywords', 'content' => 'these, are, some, meta, keywords']];
        $this->assertHtml($expected, $result);

        $result = $this->Html->meta('description', 'this is the meta description');
        $expected = ['meta' => ['name' => 'description', 'content' => 'this is the meta description']];
        $this->assertHtml($expected, $result);

        $result = $this->Html->meta('robots', 'ALL');
        $expected = ['meta' => ['name' => 'robots', 'content' => 'ALL']];
        $this->assertHtml($expected, $result);

        $result = $this->Html->meta('viewport', 'width=device-width');
        $expected = [
            'meta' => ['name' => 'viewport', 'content' => 'width=device-width'],
        ];
        $this->assertHtml($expected, $result);

        $result = $this->Html->meta(['property' => 'og:site_name', 'content' => 'CakePHP']);
        $expected = [
            'meta' => ['property' => 'og:site_name', 'content' => 'CakePHP'],
        ];
        $this->assertHtml($expected, $result);

        $result = $this->Html->meta(['link' => 'http://example.com/manifest', 'rel' => 'manifest']);
        $expected = [
            'link' => ['href' => 'http://example.com/manifest', 'rel' => 'manifest'],
        ];
        $this->assertHtml($expected, $result);
    }

    /**
     * testStyle method
     */
    public function testStyle(): void
    {
        $result = $this->Html->style(['display' => 'none', 'margin' => '10px']);
        $this->assertSame('display:none; margin:10px;', $result);

        $result = $this->Html->style(['display' => 'none', 'margin' => '10px'], false);
        $this->assertSame("display:none;\nmargin:10px;", $result);
    }

    /**
     * testScriptWithFullBase method
     */
    /*
    public function testScriptWithFullBase(): void
    {
        $here = $this->Html->Url->build('/', ['fullBase' => true]);

        $result = $this->Html->script('foo', ['fullBase' => true]);
        $expected = [
            'script' => ['src' => $here . 'js/foo.js'],
        ];
        $this->assertHtml($expected, $result);

        $result = $this->Html->script(['foobar', 'bar'], ['fullBase' => true]);
        $expected = [
            ['script' => ['src' => $here . 'js/foobar.js']],
            '/script',
            ['script' => ['src' => $here . 'js/bar.js']],
            '/script',
        ];
        $this->assertHtml($expected, $result);
    }*/

    /**
     * testNestedList method
     */
    public function testNestedList(): void
    {
        $list = [
            'Item 1',
            'Item 2' => [
                'Item 2.1',
            ],
            'Item 3',
            'Item 4' => [
                'Item 4.1',
                'Item 4.2',
                'Item 4.3' => [
                    'Item 4.3.1',
                    'Item 4.3.2',
                ],
            ],
            'Item 5' => [
                'Item 5.1',
                'Item 5.2',
            ],
        ];

        $result = $this->Html->nestedList($list);
        $expected = [
            '<ul',
            '<li', 'Item 1', '/li',
            '<li', 'Item 2',
            '<ul', '<li', 'Item 2.1', '/li', '/ul',
            '/li',
            '<li', 'Item 3', '/li',
            '<li', 'Item 4',
            '<ul',
            '<li', 'Item 4.1', '/li',
            '<li', 'Item 4.2', '/li',
            '<li', 'Item 4.3',
            '<ul',
            '<li', 'Item 4.3.1', '/li',
            '<li', 'Item 4.3.2', '/li',
            '/ul',
            '/li',
            '/ul',
            '/li',
            '<li', 'Item 5',
            '<ul',
            '<li', 'Item 5.1', '/li',
            '<li', 'Item 5.2', '/li',
            '/ul',
            '/li',
            '/ul',
        ];
        $this->assertHtml($expected, $result);

        $result = $this->Html->nestedList($list);
        $this->assertHtml($expected, $result);

        $result = $this->Html->nestedList($list, ['tag' => 'ol']);
        $expected = [
            '<ol',
            '<li', 'Item 1', '/li',
            '<li', 'Item 2',
            '<ol', '<li', 'Item 2.1', '/li', '/ol',
            '/li',
            '<li', 'Item 3', '/li',
            '<li', 'Item 4',
            '<ol',
            '<li', 'Item 4.1', '/li',
            '<li', 'Item 4.2', '/li',
            '<li', 'Item 4.3',
            '<ol',
            '<li', 'Item 4.3.1', '/li',
            '<li', 'Item 4.3.2', '/li',
            '/ol',
            '/li',
            '/ol',
            '/li',
            '<li', 'Item 5',
            '<ol',
            '<li', 'Item 5.1', '/li',
            '<li', 'Item 5.2', '/li',
            '/ol',
            '/li',
            '/ol',
        ];
        $this->assertHtml($expected, $result);

        $result = $this->Html->nestedList($list, ['tag' => 'ol']);
        $this->assertHtml($expected, $result);

        $result = $this->Html->nestedList($list, ['class' => 'list']);
        $expected = [
            ['ul' => ['class' => 'list']],
            '<li', 'Item 1', '/li',
            '<li', 'Item 2',
            ['ul' => ['class' => 'list']], '<li', 'Item 2.1', '/li', '/ul',
            '/li',
            '<li', 'Item 3', '/li',
            '<li', 'Item 4',
            ['ul' => ['class' => 'list']],
            '<li', 'Item 4.1', '/li',
            '<li', 'Item 4.2', '/li',
            '<li', 'Item 4.3',
            ['ul' => ['class' => 'list']],
            '<li', 'Item 4.3.1', '/li',
            '<li', 'Item 4.3.2', '/li',
            '/ul',
            '/li',
            '/ul',
            '/li',
            '<li', 'Item 5',
            ['ul' => ['class' => 'list']],
            '<li', 'Item 5.1', '/li',
            '<li', 'Item 5.2', '/li',
            '/ul',
            '/li',
            '/ul',
        ];
        $this->assertHtml($expected, $result);

        $result = $this->Html->nestedList($list, [], ['class' => 'item']);
        $expected = [
            '<ul',
            ['li' => ['class' => 'item']], 'Item 1', '/li',
            ['li' => ['class' => 'item']], 'Item 2',
            '<ul', ['li' => ['class' => 'item']], 'Item 2.1', '/li', '/ul',
            '/li',
            ['li' => ['class' => 'item']], 'Item 3', '/li',
            ['li' => ['class' => 'item']], 'Item 4',
            '<ul',
            ['li' => ['class' => 'item']], 'Item 4.1', '/li',
            ['li' => ['class' => 'item']], 'Item 4.2', '/li',
            ['li' => ['class' => 'item']], 'Item 4.3',
            '<ul',
            ['li' => ['class' => 'item']], 'Item 4.3.1', '/li',
            ['li' => ['class' => 'item']], 'Item 4.3.2', '/li',
            '/ul',
            '/li',
            '/ul',
            '/li',
            ['li' => ['class' => 'item']], 'Item 5',
            '<ul',
            ['li' => ['class' => 'item']], 'Item 5.1', '/li',
            ['li' => ['class' => 'item']], 'Item 5.2', '/li',
            '/ul',
            '/li',
            '/ul',
        ];
        $this->assertHtml($expected, $result);

        $result = $this->Html->nestedList($list, [], ['even' => 'even', 'odd' => 'odd']);
        $expected = [
            '<ul',
            ['li' => ['class' => 'odd']], 'Item 1', '/li',
            ['li' => ['class' => 'even']], 'Item 2',
            '<ul', ['li' => ['class' => 'odd']], 'Item 2.1', '/li', '/ul',
            '/li',
            ['li' => ['class' => 'odd']], 'Item 3', '/li',
            ['li' => ['class' => 'even']], 'Item 4',
            '<ul',
            ['li' => ['class' => 'odd']], 'Item 4.1', '/li',
            ['li' => ['class' => 'even']], 'Item 4.2', '/li',
            ['li' => ['class' => 'odd']], 'Item 4.3',
            '<ul',
            ['li' => ['class' => 'odd']], 'Item 4.3.1', '/li',
            ['li' => ['class' => 'even']], 'Item 4.3.2', '/li',
            '/ul',
            '/li',
            '/ul',
            '/li',
            ['li' => ['class' => 'odd']], 'Item 5',
            '<ul',
            ['li' => ['class' => 'odd']], 'Item 5.1', '/li',
            ['li' => ['class' => 'even']], 'Item 5.2', '/li',
            '/ul',
            '/li',
            '/ul',
        ];
        $this->assertHtml($expected, $result);

        $result = $this->Html->nestedList($list, ['class' => 'list'], ['class' => 'item']);
        $expected = [
            ['ul' => ['class' => 'list']],
            ['li' => ['class' => 'item']], 'Item 1', '/li',
            ['li' => ['class' => 'item']], 'Item 2',
            ['ul' => ['class' => 'list']], ['li' => ['class' => 'item']], 'Item 2.1', '/li', '/ul',
            '/li',
            ['li' => ['class' => 'item']], 'Item 3', '/li',
            ['li' => ['class' => 'item']], 'Item 4',
            ['ul' => ['class' => 'list']],
            ['li' => ['class' => 'item']], 'Item 4.1', '/li',
            ['li' => ['class' => 'item']], 'Item 4.2', '/li',
            ['li' => ['class' => 'item']], 'Item 4.3',
            ['ul' => ['class' => 'list']],
            ['li' => ['class' => 'item']], 'Item 4.3.1', '/li',
            ['li' => ['class' => 'item']], 'Item 4.3.2', '/li',
            '/ul',
            '/li',
            '/ul',
            '/li',
            ['li' => ['class' => 'item']], 'Item 5',
            ['ul' => ['class' => 'list']],
            ['li' => ['class' => 'item']], 'Item 5.1', '/li',
            ['li' => ['class' => 'item']], 'Item 5.2', '/li',
            '/ul',
            '/li',
            '/ul',
        ];
        $this->assertHtml($expected, $result);
    }

    /**
     * testTag method
     */
    public function testTag(): void
    {
        $result = $this->Html->tag('div', 'text');
        $this->assertHtml(['<div', 'text', '/div'], $result);

        $result = $this->Html->tag('div', '<text>', ['class' => 'class-name', 'escape' => true]);
        $expected = ['div' => ['class' => 'class-name'], '&lt;text&gt;', '/div'];
        $this->assertHtml($expected, $result);
    }

    /**
     * testDiv method
     */
    public function testDiv(): void
    {
        $result = $this->Html->div('class-name');
        $expected = ['div' => ['class' => 'class-name']];
        $this->assertHtml($expected, $result);

        $result = $this->Html->div('class-name', 'text');
        $expected = ['div' => ['class' => 'class-name'], 'text', '/div'];
        $this->assertHtml($expected, $result);

        $result = $this->Html->div('class-name', '<text>', ['escape' => true]);
        $expected = ['div' => ['class' => 'class-name'], '&lt;text&gt;', '/div'];
        $this->assertHtml($expected, $result);

        $evilKey = '><script>alert(1)</script>';
        $options = [$evilKey => 'some value'];
        $result = $this->Html->div('class-name', '', $options);
        $expected = '<div &gt;&lt;script&gt;alert(1)&lt;/script&gt;="some value" class="class-name"></div>';
        $this->assertSame($expected, $result);
    }

    /**
     * testPara method
     */
    public function testPara(): void
    {
        $result = $this->Html->para('class-name', null);
        $expected = ['p' => ['class' => 'class-name']];
        $this->assertHtml($expected, $result);

        $result = $this->Html->para('class-name', '');
        $expected = ['p' => ['class' => 'class-name'], '/p'];
        $this->assertHtml($expected, $result);

        $result = $this->Html->para('class-name', 'text');
        $expected = ['p' => ['class' => 'class-name'], 'text', '/p'];
        $this->assertHtml($expected, $result);

        $result = $this->Html->para('class-name', '<text>', ['escape' => true]);
        $expected = ['p' => ['class' => 'class-name'], '&lt;text&gt;', '/p'];
        $this->assertHtml($expected, $result);

        $result = $this->Html->para('class-name', 'text"', ['escape' => false]);
        $expected = ['p' => ['class' => 'class-name'], 'text"', '/p'];
        $this->assertHtml($expected, $result);

        $result = $this->Html->para(null, null);
        $expected = ['p' => []];
        $this->assertHtml($expected, $result);

        $result = $this->Html->para(null, 'text');
        $expected = ['p' => [], 'text', '/p'];
        $this->assertHtml($expected, $result);
    }

    /**
     * testMedia method
     */
    public function testMedia(): void
    {
        $result = $this->Html->media('video.webm');
        $expected = ['video' => ['src' => 'files/video.webm'], '/video'];

        $this->assertHtml($expected, $result);

        $result = $this->Html->media('video.webm', [
            'text' => 'Your browser does not support the HTML5 Video element.',
        ]);
        $expected = ['video' => ['src' => 'files/video.webm'], 'Your browser does not support the HTML5 Video element.', '/video'];
        $this->assertHtml($expected, $result);

        $result = $this->Html->media('video.webm', ['autoload', 'muted' => 'muted']);
        $expected = [
            'video' => [
                'src' => 'files/video.webm',
                'autoload' => 'autoload',
                'muted' => 'muted',
            ],
            '/video',
        ];
        $this->assertHtml($expected, $result);

        $result = $this->Html->media(
            ['video.webm', ['src' => 'video.ogv', 'type' => "video/ogg; codecs='theora, vorbis'"]],
            ['pathPrefix' => 'videos/', 'poster' => 'poster.jpg', 'text' => 'Your browser does not support the HTML5 Video element.']
        );
        $expected = [
            //'video' => ['poster' => Configure::read('App.imageBaseUrl') . 'poster.jpg'],
            'video' => ['poster' => '' . 'poster.jpg'],
            ['source' => ['src' => 'videos/video.webm', 'type' => 'video/webm']],
            ['source' => ['src' => 'videos/video.ogv', 'type' => 'video/ogg; codecs=&#039;theora, vorbis&#039;']],
            'Your browser does not support the HTML5 Video element.',
            '/video',
        ];
        $this->assertHtml($expected, $result);

        $result = $this->Html->media('video.ogv', ['tag' => 'video']);
        $expected = ['video' => ['src' => 'files/video.ogv'], '/video'];
        $this->assertHtml($expected, $result);

        $result = $this->Html->media('audio.mp3');
        $expected = ['audio' => ['src' => 'files/audio.mp3'], '/audio'];
        $this->assertHtml($expected, $result);

        $result = $this->Html->media(
            [['src' => 'video.mov', 'type' => 'video/mp4'], 'video.webm']
        );
        $expected = [
            '<video',
            ['source' => ['src' => 'files/video.mov', 'type' => 'video/mp4']],
            ['source' => ['src' => 'files/video.webm', 'type' => 'video/webm']],
            '/video',
        ];
        $this->assertHtml($expected, $result);

        $result = $this->Html->media(null, ['src' => 'video.webm']);
        $expected = [
            'video' => ['src' => 'files/video.webm'],
            '/video',
        ];
        $this->assertHtml($expected, $result);
    }
}
