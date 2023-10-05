<?php

use Efabrica\NeoForms\Control\SelectBox;
use PHPUnit\Framework\TestCase;

class SelectBoxTest extends TestCase
{
    public function testConstructor(): void
    {
        $items = ['foo', 'bar', 'baz'];
        $box = new SelectBox('box', $items);
        $this->assertEquals($items, $box->getItems());
        $this->assertEquals($items, $box->getItems(true));
    }

    public function testSetItems(): void
    {
        $items = ['foo', 'bar', 'baz'];
        $box = new SelectBox('box');
        $box->setItems($items);
        $this->assertEquals($items, $box->getItems());
        $this->assertEquals($items, $box->getItems(true));
    }

    public function testGetItemsTree(): void
    {
        $items = ['foo' => 'Foo', 'bar' => ['Bar', 'barbar'], 'baz' => 'Baz'];
        $box = new SelectBox('box', $items);
        $this->assertEquals($items, $box->getItems(true));
    }

    public function testSetGetItemsTree(): void
    {
        $items = ['foo' => 'Foo', 'bar' => ['Bar', 'barbar'], 'baz' => 'Baz'];
        $box = new SelectBox('box');
        $box->setItems($items);
        $this->assertEquals($items, $box->getItems(true));
    }

    public function testGetItemsTreeFalse(): void
    {
        $items = ['foo' => 'Foo', 'bar' => ['Bar', 'barbar'], 'baz' => 'Baz'];
        $box = new SelectBox('box', $items);
        $this->assertEquals(['foo' => 'Foo', 'Bar', 'barbar', 'baz' => 'Baz'], $box->getItems());
    }

    public function testSetGetItemsTreeFalse(): void
    {
        $items = ['foo' => 'Foo', 'bar' => ['Bar', 'barbar'], 'baz' => 'Baz'];
        $box = new SelectBox('box');
        $box->setItems($items);
        $this->assertEquals(['foo' => 'Foo', 'Bar', 'barbar', 'baz' => 'Baz'], $box->getItems());
    }

    public function testEmptyItems(): void
    {
        $box = new SelectBox('box');
        $this->assertEquals([], $box->getItems());
        $this->assertEquals([], $box->getItems(true));
    }
}
