<?php

declare(strict_types=1);

namespace Chiron\View\Tests\Helper;

use Chiron\View\TemplatePath;
use PHPUnit\Framework\TestCase;
use Chiron\View\StringTemplate;
use Chiron\View\Helper\HtmlHelper;

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
}
