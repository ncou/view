<?php

declare(strict_types=1);

namespace Chiron\View\Tests;

use Chiron\View\StringTemplate;
use PHPUnit\Framework\TestCase;

class StringTemplateTest extends TestCase
{
    /**
     * Test formatting strings.
     */
    public function testFormat(): void
    {
        $templates = [
            'link'   => '<a href="{{url}}">{{text}}</a>',
            'text'   => '{{text}}',
            'custom' => '<custom {{standard}} v1="{{var1}}" v2="{{var2}}" />',
        ];

        $template = new StringTemplate($templates);

        $result = $template->format('text', ['text' => '']);
        $this->assertSame('', $result);

        $result = $template->format('text', []);
        $this->assertSame('', $result);

        $result = $template->format('link', [
            'url'  => '/',
            'text' => 'example',
        ]);
        $this->assertSame('<a href="/">example</a>', $result);

        $result = $template->format('custom', [
            'standard'     => 'default',
            'templateVars' => ['var1' => 'foo'],
        ]);
        $this->assertSame('<custom default v1="foo" v2="" />', $result);
    }

    /**
     * Test formatting strings with URL encoding
     */
    public function testFormatUrlEncoding(): void
    {
        $templates = [
            'test' => '<img src="/img/foo%20bar.jpg">{{text}}',
        ];
        $template = new StringTemplate($templates);

        $result = $template->format('test', ['text' => 'stuff!']);
        $this->assertSame('<img src="/img/foo%20bar.jpg">stuff!', $result);
    }

    /**
     * Formatting array data should not trigger errors.
     */
    public function testFormatArrayData(): void
    {
        $templates = [
            'link' => '<a href="{{url}}">{{text}}</a>',
        ];
        $template = new StringTemplate($templates);

        $result = $template->format('link', [
            'url'  => '/',
            'text' => ['example', 'text'],
        ]);
        $this->assertSame('<a href="/">exampletext</a>', $result);

        $result = $template->format('link', [
            'url'  => '/',
            'text' => ['key' => 'example', 'text'],
        ]);
        $this->assertSame('<a href="/">exampletext</a>', $result);
    }

    /**
     * Test formatting a missing template.
     */
    public function testFormatMissingTemplate(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot find template named \'missing\'');
        $templates = [
            'text' => '{{text}}',
        ];
        $template = new StringTemplate($templates);
        $template->format('missing', ['text' => 'missing']);
    }

    /**
     * Test formatting compact attributes.
     */
    public function testFormatAttributesCompact(): void
    {
        $template = new StringTemplate();

        $attrs = ['disabled' => true, 'selected' => 1, 'checked' => '1', 'multiple' => 'multiple'];
        $result = $template->formatAttributes($attrs);
        $this->assertSame(
            ' disabled="disabled" selected="selected" checked="checked" multiple="multiple"',
            $result
        );

        $attrs = ['disabled' => false, 'selected' => 0, 'checked' => '0', 'multiple' => null];
        $result = $template->formatAttributes($attrs);
        $this->assertSame(
            '',
            $result
        );
    }

    /**
     * Test formatting normal attributes.
     */
    public function testFormatAttributes(): void
    {
        $template = new StringTemplate();

        $attrs = ['batman'];
        $result = $template->formatAttributes($attrs);
        $this->assertSame(
            ' batman="batman"',
            $result
        );

        $attrs = ['name' => 'bruce', 'data-hero' => '<batman>', 'spellcheck' => 'true'];
        $result = $template->formatAttributes($attrs);
        $this->assertSame(
            ' name="bruce" data-hero="&lt;batman&gt;" spellcheck="true"',
            $result
        );

        $attrs = ['escape' => false, 'name' => 'bruce', 'data-hero' => '<batman>'];
        $result = $template->formatAttributes($attrs);
        $this->assertSame(
            ' name="bruce" data-hero="<batman>"',
            $result
        );

        $attrs = ['name' => 'bruce', 'data-hero' => '<batman>'];
        $result = $template->formatAttributes($attrs, ['name']);
        $this->assertSame(
            ' data-hero="&lt;batman&gt;"',
            $result
        );

        $attrs = ['name' => 'bruce', 'data-hero' => '<batman>', 'templateVars' => ['foo' => 'bar']];
        $result = $template->formatAttributes($attrs, ['name']);
        $this->assertSame(
            ' data-hero="&lt;batman&gt;"',
            $result
        );

        $evilKey = '><script>alert(1)</script>';
        $attrs = [$evilKey => 'some value'];

        $result = $template->formatAttributes($attrs);
        $this->assertSame(
            ' &gt;&lt;script&gt;alert(1)&lt;/script&gt;="some value"',
            $result
        );
    }

    /**
     * Test formatting array attributes.
     */
    public function testFormatAttributesArray(): void
    {
        $template = new StringTemplate();

        $attrs = ['name' => ['bruce', 'wayne']];
        $result = $template->formatAttributes($attrs);
        $this->assertSame(
            ' name="bruce wayne"',
            $result
        );
    }
}
