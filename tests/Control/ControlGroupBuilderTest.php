<?php

namespace Tests\Efabrica\NeoForms\Control;

use Efabrica\NeoForms\Build\NeoForm;
use Nette\Utils\Html;
use PHPUnit\Framework\TestCase;

class ControlGroupBuilderTest extends TestCase
{
    public function testEndReturnsParentAfterColOnRow(): void
    {
        $form = new NeoForm();
        $row = $form->row();
        $col = $row->col('6');
        $this->assertSame($row, $col->end());
    }

    public function testEndOnTopLevelBuilderReturnsItself(): void
    {
        $form = new NeoForm();
        $group = $form->group('x');
        $this->assertSame($group, $group->end());
    }

    public function testEndSupportsMultiLevelNesting(): void
    {
        $form = new NeoForm();
        $row = $form->row();
        $col = $row->col('6');
        $inner = $col->group('inner');
        $this->assertSame($col, $inner->end());
        $this->assertSame($row, $inner->end()->end());
    }

    public function testFluentChainAcrossSiblingsViaEnd(): void
    {
        // addX() returns the underlying control, not the builder, so end() must be
        // called on the builder itself (after col()/row()/group()), not chained
        // directly off an addX() call.
        $form = new NeoForm();
        $row = $form->row();
        $colB = $row->col('6');
        $colB->addText('b');
        $colC = $colB->end()->col('6');
        $colC->addText('c');
        $this->assertNotNull($form->getComponent('b'));
        $this->assertNotNull($form->getComponent('c'));
    }

    public function testNamedRowReusesUnderlyingGroup(): void
    {
        // NeoForm::group() wraps a fresh ControlGroupBuilder around the same
        // underlying ControlGroup on every call for a given name (the builder
        // itself is not cached/reused, only the group it wraps).
        $form = new NeoForm();
        $form->row('main');
        $form->row('main');
        $this->assertCount(1, $form->getGroups());
    }

    public function testGroupAppliesLabelAndClassViaSharedFactory(): void
    {
        $form = new NeoForm();
        $form->group('names', 'col-6', 'My Names');

        $group = $form->getGroup('names');
        $this->assertNotNull($group);
        $this->assertSame('My Names', $group->getOption('label'));
        $container = $group->getOption('container');
        $this->assertInstanceOf(Html::class, $container);
        $this->assertStringContainsString('col-6', (string)$container->getAttribute('class'));
    }

    public function testGroupLabelDefaultsToName(): void
    {
        $form = new NeoForm();
        $form->group('billing');
        $this->assertSame('billing', $form->getGroup('billing')?->getOption('label'));
    }

    public function testNestedBuilderGroupAppliesLabelAndClass(): void
    {
        $form = new NeoForm();
        $form->group('outer')->group('inner', 'col-3', 'Inner');

        $children = $form->getGroup('outer')?->getOption('children');
        $this->assertIsArray($children);
        $child = $children['inner'];
        $this->assertSame('Inner', $child->getOption('label'));
        $container = $child->getOption('container');
        $this->assertInstanceOf(Html::class, $container);
        $this->assertStringContainsString('col-3', (string)$container->getAttribute('class'));
    }
}
