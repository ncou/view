<?php

declare(strict_types=1);

namespace Chiron\View\Tests\Helper;

use Chiron\View\Helper\UrlHelper;

//https://github.com/cakephp/cakephp/blob/32e3c532fea8abe2db8b697f07dfddf4dfc134ca/tests/TestCase/View/Helper/UrlHelperTest.php

class UrlHelperTest extends AbstractHelperTestCase
{
    /**
     * Helper to be tested
     *
     * @var \Chiron\View\Helper\UrlHelper
     */
    protected UrlHelper $Helper;

    /**
     * setUp method
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->Helper = new UrlHelper();
    }

    /**
     * Ensure HTML escaping of URL params. So link addresses are valid and not exploited
     */
    public function testBuildUrlConversion(): void
    {
        //$this->builder->connect('/:controller/:action/*');

        $result = $this->Helper->build('/controller/action/1');
        $this->assertSame('/controller/action/1', $result);

        $result = $this->Helper->build('/controller/action/1?one=1&two=2');
        $this->assertSame('/controller/action/1?one=1&amp;two=2', $result);

/*
        $result = $this->Helper->build(['controller' => 'Posts', 'action' => 'index', '?' => ['page' => '1" onclick="alert(\'XSS\');"']]);
        $this->assertSame('/posts?page=1%22+onclick%3D%22alert%28%27XSS%27%29%3B%22', $result);
*/

        $result = $this->Helper->build('/controller/action/1/param:this+one+more');
        $this->assertSame('/controller/action/1/param:this+one+more', $result);

        $result = $this->Helper->build('/controller/action/1/param:this%20one%20more');
        $this->assertSame('/controller/action/1/param:this%20one%20more', $result);

        $result = $this->Helper->build('/controller/action/1/param:%7Baround%20here%7D%5Bthings%5D%5Bare%5D%24%24');
        $this->assertSame('/controller/action/1/param:%7Baround%20here%7D%5Bthings%5D%5Bare%5D%24%24', $result);

/*
        $result = $this->Helper->build([
            'controller' => 'Posts', 'action' => 'index',
            '?' => ['param' => '%7Baround%20here%7D%5Bthings%5D%5Bare%5D%24%24'],
        ]);
        $this->assertSame('/posts?param=%257Baround%2520here%257D%255Bthings%255D%255Bare%255D%2524%2524', $result);

        $result = $this->Helper->build([
            'controller' => 'Posts', 'action' => 'index',
            '?' => ['one' => 'value', 'two' => 'value', 'three' => 'purple', 'page' => '1'],
        ]);
        $this->assertSame('/posts?one=value&amp;two=value&amp;three=purple&amp;page=1', $result);
*/
    }

    public function testBuildUrlConversionUnescaped(): void
    {
        $result = $this->Helper->build('/controller/action/1?one=1&two=2', ['escape' => false]);
        $this->assertSame('/controller/action/1?one=1&two=2', $result);

/*
        $result = $this->Helper->build([
            'controller' => 'Posts',
            'action' => 'view',
            '?' => [
                'k' => 'v',
                '1' => '2',
                'param' => '%7Baround%20here%7D%5Bthings%5D%5Bare%5D%24%24',
            ],
        ], ['escape' => false]);
        $this->assertSame('/posts/view?k=v&1=2&param=%257Baround%2520here%257D%255Bthings%255D%255Bare%255D%2524%2524', $result);
        */
    }

    /**
     * test assetUrl application
     */
    public function testAssetUrl(): void
    {
        /*
        $this->Helper->webroot = '';
        $result = $this->Helper->assetUrl('js/post.js', ['fullBase' => true]);
        $this->assertSame(Router::fullBaseUrl() . '/js/post.js', $result);
*/
        $result = $this->Helper->assetUrl('foo.jpg', ['pathPrefix' => 'img/']);
        $this->assertSame('img/foo.jpg', $result);

/*
        $result = $this->Helper->assetUrl('foo.jpg', ['fullBase' => true]);
        $this->assertSame(Router::fullBaseUrl() . '/foo.jpg', $result);
*/

        $result = $this->Helper->assetUrl('style', ['ext' => '.css']);
        $this->assertSame('style.css', $result);

        $result = $this->Helper->assetUrl('dir/sub dir/my image', ['ext' => '.jpg']);
        $this->assertSame('dir/sub%20dir/my%20image.jpg', $result);

        $result = $this->Helper->assetUrl('foo.jpg?one=two&three=four');
        $this->assertSame('foo.jpg?one=two&amp;three=four', $result);

        $result = $this->Helper->assetUrl('x:"><script>alert(1)</script>');
        $this->assertSame('x:&quot;&gt;&lt;script&gt;alert(1)&lt;/script&gt;', $result);

        $result = $this->Helper->assetUrl('dir/big+tall/image', ['ext' => '.jpg']);
        $this->assertSame('dir/big%2Btall/image.jpg', $result);
    }

    /**
     * Test assetUrl and data uris
     */
    public function testAssetUrlDataUri(): void
    {
        /*
        $request = $this->View->getRequest()
            ->withAttribute('base', 'subdir')
            ->withAttribute('webroot', 'subdir/');

        $this->View->setRequest($request);
        Router::setRequest($request);
        */

        $data = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAUAAAAFCAYAAACNbyblAAAAHElEQVQI12P4' .
            '/8/w38GIAXDIBKE0DHxgljNBAAO9TXL0Y4OHwAAAABJRU5ErkJggg==';
        $result = $this->Helper->assetUrl($data);
        $this->assertSame($data, $result);

        $data = 'data:image/png;base64,<evil>';
        $result = $this->Helper->assetUrl($data);
        $this->assertSame(e($data), $result);
    }

    /**
     * Tests assetUrl() with full base URL.
     */
    public function testAssetUrlFullBase(): void
    {
        /*
        $result = $this->Helper->assetUrl('img/foo.jpg', ['fullBase' => true]);
        $this->assertSame(Router::fullBaseUrl() . '/img/foo.jpg', $result);
*/

        $result = $this->Helper->assetUrl('img/foo.jpg', ['fullBase' => 'https://xyz/']);
        $this->assertSame('https://xyz/img/foo.jpg', $result);
    }

    /**
     * test script()
     */
    public function testScript(): void
    {
        /*
        $this->Helper->webroot = '';
        $result = $this->Helper->script(
            'post.js',
            ['fullBase' => true]
        );
        $this->assertSame(Router::fullBaseUrl() . '/js/post.js', $result);
        */
    }

    /**
     * test image()
     */
    public function testImage(): void
    {
        $result = $this->Helper->image('foo.jpg');
        $this->assertSame('assets/img/foo.jpg', $result);

/*
        $result = $this->Helper->image('foo.jpg', ['fullBase' => true]);
        $this->assertSame(Router::fullBaseUrl() . '/assets/img/foo.jpg', $result);
*/

        $result = $this->Helper->image('dir/sub dir/my image.jpg');
        $this->assertSame('assets/img/dir/sub%20dir/my%20image.jpg', $result);

        $result = $this->Helper->image('foo.jpg?one=two&three=four');
        $this->assertSame('assets/img/foo.jpg?one=two&amp;three=four', $result);

        $result = $this->Helper->image('dir/big+tall/image.jpg');
        $this->assertSame('assets/img/dir/big%2Btall/image.jpg', $result);

        $result = $this->Helper->image('cid:foo.jpg');
        $this->assertSame('cid:foo.jpg', $result);

        $result = $this->Helper->image('CID:foo.jpg');
        $this->assertSame('CID:foo.jpg', $result);
    }

    /**
     * test css
     */
    public function testCss(): void
    {
        $result = $this->Helper->css('style');
        $this->assertSame('assets/css/style.css', $result);
    }
}
