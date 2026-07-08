<?php

namespace Tests\Efabrica\NeoForms\Render\Template;

use Efabrica\NeoForms\Build\NeoForm;
use Efabrica\NeoForms\Render\Template\TailwindFormTemplate;
use Nette\Utils\Html;
use PHPUnit\Framework\TestCase;

class TailwindFormTemplateTest extends TestCase
{
    private TailwindFormTemplate $template;

    protected function setUp(): void
    {
        $this->template = new TailwindFormTemplate();
    }

    public function testTextInputGetsInputClass(): void
    {
        $form = new NeoForm();
        $control = $form->addText('foo', 'Foo');

        $html = (string)$this->template->formInput($control, [], Html::el());

        $this->assertStringContainsString('rounded-md', $html);
        $this->assertStringNotContainsString('border-red-500', $html);
    }

    public function testTextInputGetsInvalidClassWhenControlHasErrors(): void
    {
        $form = new NeoForm();
        $control = $form->addText('foo', 'Foo');
        $control->addError('Required');

        $html = (string)$this->template->formInput($control, [], Html::el());

        $this->assertStringContainsString('border-red-500', $html);
    }

    public function testCheckboxRendersWrapperWithCaption(): void
    {
        $form = new NeoForm();
        $control = $form->addCheckbox('agree', 'I agree to the terms');

        $html = (string)$this->template->formInput($control, [], Html::el());

        $this->assertStringContainsString('flex items-center', $html);
        $this->assertStringContainsString('I agree to the terms', $html);
    }

    public function testButtonGetsButtonClass(): void
    {
        $form = new NeoForm();
        $control = $form->addButton('save', 'Save');

        $html = (string)$this->template->formInput($control, [], Html::el());

        $this->assertStringContainsString('bg-indigo-600', $html);
    }

    public function testOverridingClassPropertyChangesOutput(): void
    {
        $template = new class extends TailwindFormTemplate {
            protected string $inputClass = 'my-custom-input';
        };

        $form = new NeoForm();
        $control = $form->addText('foo', 'Foo');

        $html = (string)$template->formInput($control, [], Html::el());

        $this->assertStringContainsString('my-custom-input', $html);
    }

    public function testFormErrorsReturnsEmptyHtmlWhenNoErrors(): void
    {
        $html = (string)$this->template->formErrors([]);
        $this->assertSame('', $html);
    }

    public function testFormErrorsWrapsErrors(): void
    {
        $html = (string)$this->template->formErrors(['Something went wrong']);
        $this->assertStringContainsString('Something went wrong', $html);
        $this->assertStringContainsString('bg-red-50', $html);
    }
}
