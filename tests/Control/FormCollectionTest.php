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
        $this->assertCount(1, iterator_to_array($collection->getControls()));
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

    public function testRemoveExcludedKeysStripsFrameworkKeysRecursively(): void
    {
        $clazz = new stdClass();
        $clazz->{FormCollectionItem::UNIQID} = 'test';
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
    }

    public function testRemoveExcludedKeysStripsCallerSuppliedKeys(): void
    {
        $values = [
            'keep' => 'a',
            'drop' => 'b',
            'nested' => ['drop' => 'c', 'keep' => 'd'],
        ];
        NeoForm::removeExcludedKeys($values, ['drop']);
        $this->assertEquals(['keep' => 'a', 'nested' => ['keep' => 'd']], $values);
    }

    public function testConstructingCollectionsDoesNotPolluteGlobalExclusions(): void
    {
        // Regression: each FormCollection used to register its unique "__prototype{N}__" name
        // into a process-global static set (unbounded growth + cross-form leakage). The prototype
        // is now stripped structurally by getUntrustedValues(), so no global registration happens
        // and unrelated keys that merely look like a prototype name are left untouched.
        $c1 = new FormCollection('A', function () {
        });
        $c2 = new FormCollection('B', function () {
        });
        $values = [
            $c1->getPrototype()->getName() => 'x',
            $c2->getPrototype()->getName() => 'y',
            'keep' => 'z',
        ];
        NeoForm::removeExcludedKeys($values);

        $this->assertArrayHasKey($c1->getPrototype()->getName(), $values);
        $this->assertArrayHasKey($c2->getPrototype()->getName(), $values);
        $this->assertSame('z', $values['keep']);
    }

    public function testAddExcludedKeysIsFluentAndInstanceScoped(): void
    {
        $formA = new NeoForm();
        $formB = new NeoForm();

        $this->assertSame($formA, $formA->addExcludedKeys('secret'));

        // Instance state on formA must not bleed into a separate instance formB. Verified via the
        // private $excludedKeys property since observing it through getValues() would require an
        // anchored (submitted) form.
        $prop = new \ReflectionProperty(NeoForm::class, 'excludedKeys');
        $this->assertSame(['secret' => 'secret'], $prop->getValue($formA));
        $this->assertSame([], $prop->getValue($formB));
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

    public function testOnAddItemAndOnRemoveItemDefaultToEmptyArrays(): void
    {
        $collection = new FormCollection('Test', function () {
        });
        $this->assertSame([], $collection->onAddItem);
        $this->assertSame([], $collection->onRemoveItem);
    }

    public function testOnAddItemFiresForRealItemsNotPrototype(): void
    {
        $collection = new FormCollection('Test', function (FormCollectionItem $item) {
            $item->addText('foo', 'Bar');
        });
        $added = [];
        $collection->onAddItem[] = function (FormCollectionItem $item) use (&$added) {
            $added[] = $item;
        };

        $collection->updateChildren(['a' => ['foo' => 'x'], 'b' => ['foo' => 'y']]);

        $this->assertCount(2, $added);
        foreach ($added as $item) {
            $this->assertNotSame($collection->getPrototype(), $item);
        }
    }

    public function testOnAddItemFiresAfterFactoryAndValuesAreSet(): void
    {
        $collection = new FormCollection('Test', function (FormCollectionItem $item) {
            $item->addText('foo', 'Bar');
        });
        $seenValue = null;
        $collection->onAddItem[] = function (FormCollectionItem $item) use (&$seenValue) {
            $seenValue = $item->getComponent('foo')->getValue();
        };

        $collection->updateChildren(['a' => ['foo' => 'hello']]);

        $this->assertSame('hello', $seenValue);
    }

    public function testOnRemoveItemFiresWhenChildDroppedByUpdateChildren(): void
    {
        $collection = new FormCollection('Test', function (FormCollectionItem $item) {
            $item->addText('foo', 'Bar');
        });
        $collection->updateChildren(['a' => ['foo' => 'x'], 'b' => ['foo' => 'y']]);

        $removed = [];
        $collection->onRemoveItem[] = function (FormCollectionItem $item) use (&$removed) {
            $removed[] = $item;
        };
        $collection->updateChildren(['a' => ['foo' => 'x']]);

        $this->assertCount(1, $removed);
    }
}
