<?php

namespace Tests\Efabrica\NeoForms\Build;

use Efabrica\NeoForms\Build\NeoForm;
use PHPUnit\Framework\TestCase;

class NeoFormTest extends TestCase
{
    public function testViewModeDefaultsToFalse(): void
    {
        $form = new NeoForm();
        $this->assertFalse($form->isViewMode());
        $this->assertFalse($form->hasReadonlyInputs());
    }

    public function testSetViewMode(): void
    {
        $form = new NeoForm();
        $this->assertSame($form, $form->setViewMode());
        $this->assertTrue($form->isViewMode());
        $form->setViewMode(false);
        $this->assertFalse($form->isViewMode());
    }

    public function testSetReadonlyInputs(): void
    {
        $form = new NeoForm();
        $this->assertSame($form, $form->setReadonlyInputs());
        $this->assertTrue($form->hasReadonlyInputs());
        $form->setReadonlyInputs(false);
        $this->assertFalse($form->hasReadonlyInputs());
    }

    public function testViewModeAndReadonlyInputsAreIndependent(): void
    {
        $form = new NeoForm();
        $form->setViewMode(true);
        $this->assertTrue($form->isViewMode());
        $this->assertFalse($form->hasReadonlyInputs());
    }

    /**
     * @group legacy
     */
    public function testDeprecatedReadonlyAliasesDelegateToViewMode(): void
    {
        $form = new NeoForm();
        $this->assertSame($form, $form->setReadonly());
        $this->assertTrue($form->isReadonly());
        $this->assertTrue($form->isViewMode());

        $form->setViewMode(false);
        $this->assertFalse($form->isReadonly());
    }

    /**
     * @group legacy
     */
    public function testDeprecatedReadonlyAttrAliasesDelegateToReadonlyInputs(): void
    {
        $form = new NeoForm();
        $this->assertSame($form, $form->setReadonlyAttr());
        $this->assertTrue($form->isReadonlyAttr());
        $this->assertTrue($form->hasReadonlyInputs());

        $form->setReadonlyInputs(false);
        $this->assertFalse($form->isReadonlyAttr());
    }
}
