<?php

namespace Tests\Efabrica\NeoForms\Render\Template;

use Efabrica\NeoForms\Build\NeoForm;
use Efabrica\NeoForms\Render\Template\Bootstrap5FormTemplate;
use Nette\Utils\Html;
use PHPUnit\Framework\TestCase;

class Bootstrap5FormTemplateTest extends TestCase
{
    private Bootstrap5FormTemplate $template;

    protected function setUp(): void
    {
        $this->template = new Bootstrap5FormTemplate();
    }

    public function testTextInputGetsFormControlClass(): void
    {
        $form = new NeoForm();
        $control = $form->addText('foo', 'Foo');

        $html = (string)$this->template->formInput($control, [], Html::el());

        $this->assertStringContainsString('form-control', $html);
        $this->assertStringNotContainsString('is-invalid', $html);
    }

    public function testTextInputGetsIsInvalidClassWhenControlHasErrors(): void
    {
        $form = new NeoForm();
        $control = $form->addText('foo', 'Foo');
        $control->addError('Required');

        $html = (string)$this->template->formInput($control, [], Html::el());

        $this->assertStringContainsString('form-control', $html);
        $this->assertStringContainsString('is-invalid', $html);
    }

    public function testSelectGetsFormSelectClass(): void
    {
        $form = new NeoForm();
        $control = $form->addSelect('foo', 'Foo', ['a' => 'A', 'b' => 'B']);

        $html = (string)$this->template->formInput($control, [], Html::el());

        $this->assertStringContainsString('form-select', $html);
    }

    public function testCheckboxRendersFormCheckWrapperWithCaption(): void
    {
        $form = new NeoForm();
        $control = $form->addCheckbox('agree', 'I agree to the terms');

        $html = (string)$this->template->formInput($control, [], Html::el());

        $this->assertStringContainsString('form-check', $html);
        $this->assertStringContainsString('form-check-input', $html);
        $this->assertStringContainsString('form-check-label', $html);
        $this->assertStringContainsString('I agree to the terms', $html);
    }

    public function testButtonGetsBtnPrimaryClass(): void
    {
        $form = new NeoForm();
        $control = $form->addButton('save', 'Save');

        $html = (string)$this->template->formInput($control, [], Html::el());

        $this->assertStringContainsString('btn btn-primary', $html);
    }

    public function testFormRowWrapsInMb3Div(): void
    {
        $html = (string)$this->template->formRow(Html::el('label', 'Foo'), Html::el('input'), Html::el(), []);

        $this->assertStringContainsString('mb-3', $html);
    }

    public function testFormLabelGetsFormLabelClass(): void
    {
        $form = new NeoForm();
        $control = $form->addText('foo', 'Foo');

        $html = (string)$this->template->formLabel($control, []);

        $this->assertStringContainsString('form-label', $html);
    }

    public function testFormErrorsReturnsEmptyHtmlWhenNoErrors(): void
    {
        $html = (string)$this->template->formErrors([]);
        $this->assertSame('', $html);
    }

    public function testFormErrorsWrapsInAlertDanger(): void
    {
        $html = (string)$this->template->formErrors(['Something went wrong']);
        $this->assertStringContainsString('alert alert-danger', $html);
        $this->assertStringContainsString('Something went wrong', $html);
    }

    public function testRowErrorsWrapsInInvalidFeedback(): void
    {
        $html = (string)$this->template->rowErrors(['Required']);
        $this->assertStringContainsString('invalid-feedback', $html);
        $this->assertStringContainsString('Required', $html);
    }
}
