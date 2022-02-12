<?php

declare(strict_types=1);

namespace Chiron\Views\Tests\Engine;

use Chiron\Views\Engine\PhpRenderer;
use Chiron\Views\TemplatePath;
use PHPUnit\Framework\TestCase;

class PhpRendererTest extends TestCase
{
    public function assertTemplatePath($path, TemplatePath $templatePath, $message = null)
    {
        $message = $message ?: sprintf('Failed to assert TemplatePath contained path %s', $path);
        $this->assertEquals($path, $templatePath->getPath(), $message);
    }

    public function assertTemplatePathString($path, TemplatePath $templatePath, $message = null)
    {
        $message = $message ?: sprintf('Failed to assert TemplatePath casts to string path %s', $path);
        $this->assertEquals($path, (string) $templatePath, $message);
    }

    public function assertTemplatePathNamespace($namespace, TemplatePath $templatePath, $message = null)
    {
        $message = $message
            ?: sprintf('Failed to assert TemplatePath namespace matched %s', var_export($namespace, true));
        $this->assertEquals($namespace, $templatePath->getNamespace(), $message);
    }

    public function assertEmptyTemplatePathNamespace(TemplatePath $templatePath, $message = null)
    {
        $message = $message ?: 'Failed to assert TemplatePath namespace was empty';
        $this->assertEmpty($templatePath->getNamespace(), $message);
    }

    public function testCanAddPathWithEmptyNamespace()
    {
        $renderer = new PhpRenderer();
        $renderer->addPath(__DIR__ . '/TestAsset');
        $paths = $renderer->getPaths();
        $this->assertInternalType('array', $paths);
        $this->assertCount(1, $paths);
        $this->assertTemplatePath(__DIR__ . '/TestAsset', $paths[0]);
        $this->assertTemplatePathString(__DIR__ . '/TestAsset', $paths[0]);
        $this->assertEmptyTemplatePathNamespace($paths[0]);
    }

    public function testCanAddPathWithNamespace()
    {
        $renderer = new PhpRenderer();
        $renderer->addPath(__DIR__ . '/TestAsset', 'test');
        $paths = $renderer->getPaths();
        $this->assertInternalType('array', $paths);
        $this->assertCount(1, $paths);
        $this->assertTemplatePath(__DIR__ . '/TestAsset', $paths[0]);
        $this->assertTemplatePathString(__DIR__ . '/TestAsset', $paths[0]);
        $this->assertTemplatePathNamespace('test', $paths[0]);
    }

    public function testDelegatesRenderingToUnderlyingImplementation()
    {
        $renderer = new PhpRenderer();
        $renderer->addPath(__DIR__ . '/TestAsset');
        $result = $renderer->render('testTemplate', ['hello' => 'Hi']);
        $this->assertEquals('Hi', $result);
    }

    public function testExceptionInTemplateWithCatch()
    {
        $renderer = new PhpRenderer();
        $renderer->addPath(__DIR__ . '/TestAsset');

        try {
            $response = $renderer->render('testException');
        } catch (\Throwable $t) {
            // Simulates an error template
            $response = $renderer->render('testTemplate', [
                'hello' => 'Hi',
            ]);
        }

        $this->assertEquals('Hi', $response);
    }

    /**
     * @expectedException Exception
     */
    public function testExceptionInTemplate()
    {
        $renderer = new PhpRenderer();
        $renderer->addPath(__DIR__ . '/TestAsset');

        $renderer->render('testException');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testTemplateNotFound()
    {
        $renderer = new PhpRenderer();
        $renderer->addPath(__DIR__ . '/TestAsset');

        $renderer->render('nonExistingTemplate', []);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testPathNotRegistered()
    {
        $renderer = new PhpRenderer();

        $renderer->render('checkTemplateButFailBecauseNoPathsAreRegistered', []);
    }
}
