<?php

declare(strict_types=1);

namespace Chiron\Views\Tests;

use Chiron\Views\Tests\Fixtures\TemplateRendererMock;
use PHPUnit\Framework\TestCase;

class AttributesTraitTest extends TestCase
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
     * @coversDefaultClass  getAttributes
     */
    public function testGetAttributes()
    {
        $this->assertEmpty($this->class->getAttributes());
    }

    /**
     * @coversDefaultClass  setAttributes
     */
    public function testSetAttributes()
    {
        $this->class->setAttributes(['foo' => 'bar']);
        $this->assertArrayHasKey('foo', $this->class->getAttributes());
    }

    /**
     * @coversDefaultClass  unsetAttributes
     */
    public function testUnsetAttributes()
    {
        $this->class->setAttributes(['foo' => 'bar']);
        $this->class->unsetAttributes();
        $this->assertEmpty($this->class->getAttributes());
    }

    /**
     * @coversDefaultClass  addAttribute
     */
    public function testAddAttribute()
    {
        $this->class->addAttribute('foo', 'bar');
        $this->assertEquals('bar', $this->class->getAttribute('foo'));
    }

    /**
     * @coversDefaultClass  getAttribute
     */
    public function testGetAttribute()
    {
        $this->class->setAttributes(['foo' => 'bar']);
        $this->assertEquals('bar', $this->class->getAttribute('foo'));
    }

    /**
     * @coversDefaultClass  removeAttribute
     */
    public function testRemoveAttribute()
    {
        $this->class->setAttributes(['foo' => 'bar']);
        $this->class->removeAttribute('foo');
        $this->assertArrayNotHasKey('foo', $this->class->getAttributes());
    }

    /**
     * @coversDefaultClass  hasAttribute
     */
    public function testHasAttribute()
    {
        $this->class->setAttributes(['foo' => 'bar']);
        $this->assertTrue($this->class->hasAttribute('foo'));
        $this->assertFalse($this->class->hasAttribute('noop'));
    }
}
