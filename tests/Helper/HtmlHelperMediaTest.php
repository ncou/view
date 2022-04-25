<?php

declare(strict_types=1);

namespace Chiron\View\Tests\Helper;

use Chiron\View\TemplatePath;
use PHPUnit\Framework\TestCase;
use Chiron\View\StringTemplate;
use Chiron\View\Helper\HtmlHelper;

//https://github.com/cakephp/cakephp/blob/32e3c532fea8abe2db8b697f07dfddf4dfc134ca/tests/TestCase/View/Helper/HtmlHelperTest.php

class HtmlHelperMediaTest extends AbstractHelperTestCase
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
