<?php

namespace Tests\Efabrica\NeoForms\Control;

use Efabrica\NeoForms\Build\NeoForm;
use Efabrica\NeoForms\Control\FormCollection;
use Efabrica\NeoForms\Control\FormCollectionDiff;
use Efabrica\NeoForms\Control\FormCollectionItem;
use Nette\Forms\Controls\TextInput;
use PHPUnit\Framework\TestCase;
use stdClass;

class FormCollectionTest extends TestCase
{
    public function testConstructorSetsLabel(): void
    {
        $label = 'Test Label';
        $formFactory = function () {
        };
        $form = new NeoForm();
        $collection = $form->addCollection('test', $label, $formFactory);
        $this->assertEquals($label, $collection->getLabel());
    }

    public function testAddCollection(): void
    {
        $label = 'Test Label';
        $formFactory = function (FormCollectionItem $item) {
            $item->addText('foo', 'Bar');
        };
        $form = new NeoForm();
        $collection = $form->addCollection('test', $label, $formFactory);
        $this->assertCount(1, $form->getComponents());
        foreach ($form->getComponents() as $component) {
            $this->assertInstanceOf(FormCollection::class, $component);
        }
        $components = iterator_to_array($collection->getPrototype()->getComponents());
        $this->assertEquals(['foo'], array_keys($components));
        $this->assertEquals('foo', $components['foo']->getName());
        $this->assertEquals('Bar', $components['foo']->getCaption());
        $this->assertEquals($collection->getPrototype(), $components['foo']->getParent());
        $this->assertInstanceOf(TextInput::class, $components['foo']);
    }

    public function testConstructorSetsSingleRender(): void
    {
        $label = 'Test Label';
        $formFactory = function () {
        };
        $collection = new FormCollection($label, $formFactory);
        $this->assertTrue($collection->isSingleRender());
    }

    public function testConstructorAddsPrototype(): void
    {
        $label = 'Test Label';
        $formFactory = function (FormCollectionItem $item) {
            $item->addHidden('foo', 'bar');
        };
        $collection = new FormCollection($label, $formFactory);
        $this->assertCount(0, iterator_to_array($collection->getItems()));
        $this->assertCount(1, $collection->getComponents());
        foreach ($collection->getComponents() as $component) {
            $this->assertInstanceOf(FormCollectionItem::class, $component);
        }
        $this->assertCount(1, $collection->getControls());
        foreach ($collection->getControls() as $control) {
            $this->assertEquals('foo', $control->getName());
            $this->assertEquals('bar', $control->getValue());
        }
    }

    public function testConstructorInvokesFormFactoryOnPrototype(): void
    {
        $formFactory = new class {
            public int $x = 0;
            public ?FormCollectionItem $prototype = null;

            public function __invoke(FormCollectionItem $item)
            {
                $this->x++;
                $this->prototype = $item;
            }
        };
        $form = new NeoForm();
        $collection = $form->addCollection('foo', 'Bar', $formFactory);
        $this->assertEquals($collection->getPrototype(), $formFactory->prototype);
        $this->assertEquals(1, $formFactory->x);
    }

    public function testConstructorAddsExcludedKeys(): void
    {
        $label = 'Test Label';
        $formFactory = function () {
        };
        $collection = new FormCollection($label, $formFactory);
        $clazz = new stdClass();
        $clazz->{$collection->getPrototype()->getName()} = 'test';
        $clazz->test9 = 'test';
        $values = [
            "test" => "test",
            FormCollection::ORIGINAL_DATA => "test5",
            'test8' => [FormCollectionItem::UNIQID => "test3"],
            'test6' => $clazz,
            "test2" => "test2",
        ];
        NeoForm::removeExcludedKeys($values);
        $this->assertEquals([
            "test" => "test",
            'test8' => [],
            'test6' => $clazz,
            "test2" => "test2",
        ], $values);
        $this->assertEquals(['test9' => 'test'], (array)$clazz);
        $this->assertFalse(isset($clazz->{$collection->getPrototype()->getName()}));

    }

    public function testCleanArrayRemovesOriginalDataAndUniqidKeys(): void
    {
        $input = [
            'foo' => 'bar',
            'baz' => [
                'qux' => 'quux',
                FormCollection::ORIGINAL_DATA => 'original data',
            ],
            'items' => [
                [
                    'id' => 1,
                    'name' => 'Item 1',
                    FormCollectionItem::UNIQID => 'abc123',
                ],
                [
                    'id' => 2,
                    'name' => 'Item 2',
                    FormCollectionItem::UNIQID => 'def456',
                ],
            ],
        ];

        $expectedOutput = [
            'foo' => 'bar',
            'baz' => ['qux' => 'quux'],
            'items' => [
                ['id' => 1, 'name' => 'Item 1'],
                ['id' => 2, 'name' => 'Item 2'],
            ],
        ];

        $this->assertEquals($expectedOutput, FormCollectionDiff::cleanArray($input));
    }

    public function testCleanArrayDoesNotModifyInputArray(): void
    {
        $input = [
            'foo' => 'bar',
            'baz' => [
                'qux' => 'quux',
                FormCollection::ORIGINAL_DATA => 'original data',
            ],
        ];
        $expectedOutput = [
            'foo' => 'bar',
            'baz' => ['qux' => 'quux'],
        ];

        $this->assertEquals(FormCollectionDiff::cleanArray($input), $expectedOutput);
    }

    public function testCleanArrayReturnsEmptyArrayForEmptyInput(): void
    {
        $this->assertEquals([], FormCollectionDiff::cleanArray([]));
    }
}
