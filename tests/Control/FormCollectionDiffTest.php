<?php

namespace Tests\Efabrica\NeoForms\Control;

use Efabrica\NeoForms\Control\FormCollection;
use Efabrica\NeoForms\Control\FormCollectionDiff;
use Efabrica\NeoForms\Control\FormCollectionItem;
use Efabrica\NeoForms\Control\FormCollectionItemDiff;
use InvalidArgumentException;
use JsonException;
use PHPUnit\Framework\TestCase;

class FormCollectionDiffTest extends TestCase
{
    public function testIsNotEmpty(): void
    {
        $diff = new FormCollectionDiff([FormCollection::ORIGINAL_DATA => '{}']);
        $this->assertFalse($diff->isNotEmpty());
        $diff = new FormCollectionDiff([FormCollection::ORIGINAL_DATA => '[]']);
        $this->assertFalse($diff->isNotEmpty());
        $john = ['name' => 'John', 'age' => 30];
        $originalData = [[FormCollectionItem::UNIQID => uniqid(), ...$john]];
        $diff = new FormCollectionDiff([FormCollection::ORIGINAL_DATA => '[]', [$john]]);
        $this->assertTrue($diff->isNotEmpty());
        $diff = new FormCollectionDiff([FormCollection::ORIGINAL_DATA => json_encode($originalData)]);
        $this->assertTrue($diff->isNotEmpty());
        $diff = new FormCollectionDiff([FormCollection::ORIGINAL_DATA => json_encode($originalData), [$john]]);
        $this->assertTrue($diff->isNotEmpty());
        $diff = new FormCollectionDiff([FormCollection::ORIGINAL_DATA => json_encode($originalData), ...$originalData]);
        $this->assertFalse($diff->isNotEmpty());
    }

    public function testConstructorErrorEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new FormCollectionDiff([]);
    }

    public function testConstructorErrorJson(): void
    {
        $this->expectException(JsonException::class);
        new FormCollectionDiff([FormCollection::ORIGINAL_DATA => 'xx']);
    }


    public function testGetAddedOnly(): void
    {
        $people = [
            ['name' => 'John', 'age' => 30],
            ['name' => 'Jane', 'age' => 25],
            ['name' => 'Bob', 'age' => 40],
        ];
        $httpData = [FormCollection::ORIGINAL_DATA => '[]', ...$people];

        $formCollectionDiff = new FormCollectionDiff($httpData);

        $added = iterator_to_array($formCollectionDiff->getAdded());

        $this->assertEquals($people, $added);
    }

    public function testGetAddedAppend(): void
    {
        $john = ['name' => 'John', 'age' => 30, FormCollectionItem::UNIQID => uniqid()];
        $originalData = json_encode([$john]);
        $httpData = [
            FormCollection::ORIGINAL_DATA => $originalData,
            $john,
            ['name' => 'Jane', 'age' => 25],
        ];

        $formCollectionDiff = new FormCollectionDiff($httpData);
        $added = iterator_to_array($formCollectionDiff->getAdded());

        $this->assertEquals([['name' => 'Jane', 'age' => 25]], $added);
    }

    public function testGetModified(): void
    {
        $john = ['name' => 'John', 'age' => 30, FormCollectionItem::UNIQID => uniqid()];
        $originalData = json_encode([$john]);
        $john['age'] = 40;
        $httpData = [
            FormCollection::ORIGINAL_DATA => $originalData,
            ['name' => 'John', 'age' => 30],
            $john,
        ];

        $diff = new FormCollectionDiff($httpData);
        $added = iterator_to_array($diff->getAdded());
        $modified = iterator_to_array($diff->getModified());

        $this->assertCount(1, $added);
        $this->assertCount(1, $modified);
        $itemDiff = $modified[0];
        $this->assertInstanceOf(FormCollectionItemDiff::class, $itemDiff);
        $this->assertEquals(['age' => 40], $itemDiff->getDiff());
        $this->assertEquals(['name' => 'John', 'age' => 30], $itemDiff->getOldRow());
        $this->assertEquals(['name' => 'John', 'age' => 40], $itemDiff->getNewRow());

        $this->assertEquals([['name' => 'John', 'age' => 30]], $added);
    }

    public function testGetDeletedSame(): void
    {
        $john = ['name' => 'John', 'age' => 30, FormCollectionItem::UNIQID => uniqid()];
        $originalData = json_encode([$john]);
        $httpData = [
            FormCollection::ORIGINAL_DATA => $originalData,
            ['name' => 'John', 'age' => 30],
        ];

        $diff = new FormCollectionDiff($httpData);
        $added = iterator_to_array($diff->getAdded());
        $deleted = iterator_to_array($diff->getDeleted());
        $modified = iterator_to_array($diff->getModified());

        $this->assertEquals([['name' => 'John', 'age' => 30]], $deleted);
        $this->assertEquals([['name' => 'John', 'age' => 30]], $added);
        $this->assertEquals([], $modified);
    }

    public function testGetDeletedOnly(): void
    {
        $john = ['name' => 'John', 'age' => 30, FormCollectionItem::UNIQID => uniqid()];
        $originalData = json_encode([$john]);
        $httpData = [
            FormCollection::ORIGINAL_DATA => $originalData,
        ];

        $diff = new FormCollectionDiff($httpData);
        $added = iterator_to_array($diff->getAdded());
        $deleted = iterator_to_array($diff->getDeleted());
        $modified = iterator_to_array($diff->getModified());

        $this->assertEquals([['name' => 'John', 'age' => 30]], $deleted);
        $this->assertEquals([], $added);
        $this->assertEquals([], $modified);
    }

    public function testComplexDiff(): void
    {
        $john = ['name' => 'John', 'age' => 30, FormCollectionItem::UNIQID => uniqid()];
        $jane = ['name' => 'Jane', 'age' => 25, FormCollectionItem::UNIQID => uniqid()];
        $bob = ['name' => 'Bob', 'age' => 40, FormCollectionItem::UNIQID => uniqid()];
        $originalData = json_encode([$john, $jane, $bob]);
        $john['name'] = 'Jaquel';
        $httpData = [
            FormCollection::ORIGINAL_DATA => $originalData,
            $john,
            $bob,
            ['name' => 'Bob', 'age' => 50],
        ];

        $diff = new FormCollectionDiff($httpData);

        $added = iterator_to_array($diff->getAdded());
        $deleted = iterator_to_array($diff->getDeleted());
        $modified = iterator_to_array($diff->getModified());

        $this->assertEquals([['name' => 'Bob', 'age' => 50]], $added);
        unset($jane[FormCollectionItem::UNIQID]);
        $this->assertEquals([$jane], $deleted);
        $this->assertCount(1, $modified);
        $itemDiff = $modified[0];
        $this->assertInstanceOf(FormCollectionItemDiff::class, $itemDiff);
        $this->assertEquals(['name' => 'Jaquel'], $itemDiff->getDiff());
        $this->assertEquals(['name' => 'John', 'age' => 30], $itemDiff->getOldRow());
        $this->assertEquals(['name' => 'Jaquel', 'age' => 30], $itemDiff->getNewRow());
    }

    public function testAreArraysRecursivelyEqualReturnsTrueForEqualArrays()
    {
        $a = [
            'foo' => 'bar',
            'baz' => ['qux' => 'quux'],
        ];

        $formCollectionDiff = new FormCollectionDiffExposed([FormCollection::ORIGINAL_DATA => '{}']);
        $result = $formCollectionDiff->areArraysRecursivelyEqual($a, $a);

        $this->assertTrue($result);
    }

    public function testAreArraysRecursivelyEqualReturnsFalseForDifferentArrays()
    {
        $a = [
            'foo' => 'bar',
            'baz' => [
                'qux' => 'quux',
            ],
        ];
        $b = [
            'foo' => 'bar',
            'baz' => [
                'qux' => 'corge',
            ],
        ];

        $formCollectionDiff = new FormCollectionDiffExposed([FormCollection::ORIGINAL_DATA => '{}']);
        $result = $formCollectionDiff->areArraysRecursivelyEqual($a, $b);

        $this->assertFalse($result);
    }

    public function testAreArraysRecursivelyEqualReturnsFalseForArraysOfDifferentLengths()
    {
        $a = [
            'foo' => 'bar',
            'baz' => [
                'qux' => 'quux',
            ],
        ];
        $b = [
            'foo' => 'bar',
        ];

        $formCollectionDiff = new FormCollectionDiffExposed([FormCollection::ORIGINAL_DATA => '{}']);
        $result = $formCollectionDiff->areArraysRecursivelyEqual($a, $b);

        $this->assertFalse($result);
    }
}

class FormCollectionDiffExposed extends FormCollectionDiff
{
    public function areArraysRecursivelyEqual(array $a, array $b): bool
    {
        return parent::areArraysRecursivelyEqual($a, $b);
    }
}
