<?php

declare(strict_types=1);

namespace Chiron\View\Tests;

use Chiron\View\Tests\Fixtures\TemplateRendererMock;
use PHPUnit\Framework\TestCase;

class ExtensionTraitTest extends TestCase
{
    protected $class;

    /**
     * Setup the tests.
     */
    protected function setUp()
    {
        $this->class = new TemplateRendererMock();
    }

    /**
     * Tear down the tests.
     */
    protected function tearDown()
    {
        $this->class = null;
    }

    /**
     * @coversDefaultClass  getExtension
     */
    public function testGetExtensionDefaultValue()
    {
        $this->assertEquals('html', $this->class->getExtension());
    }

    /*
     * @coversDefaultClass  setExtension
     */
    public function testSetExtension()
    {
        $this->class->setExtension('tpl');
        $this->assertEquals('tpl', $this->class->getExtension());
    }
}
