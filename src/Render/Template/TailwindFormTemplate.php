<?php

namespace Efabrica\NeoForms\Render\Template;

use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\Button;
use Nette\Forms\Controls\Checkbox;
use Nette\Forms\Controls\MultiSelectBox;
use Nette\Forms\Controls\SelectBox;
use Nette\Forms\Controls\TextArea;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Controls\UploadControl;
use Nette\HtmlStringable;
use Nette\Utils\Html;

/**
 * A deliberately minimal Tailwind CSS starting point, not a finished design
 * system - Tailwind has no component classes to target the way Bootstrap
 * does, so hardcoding one opinionated utility-class combination would just be
 * something most Tailwind users end up overriding anyway. Override the
 * `protected string $...Class` properties below (or the render methods
 * themselves) to match your own design tokens.
 */
class TailwindFormTemplate extends NeoFormTemplate
{
    protected string $rowClass = 'mb-4';

    protected string $labelClass = 'block text-sm font-medium text-gray-700 mb-1';

    protected string $inputClass = 'block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm';

    protected string $invalidInputClass = 'border-red-500 text-red-900 focus:border-red-500 focus:ring-red-500';

    protected string $checkboxInputClass = 'h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500';

    protected string $checkboxWrapperClass = 'flex items-center';

    protected string $checkboxLabelClass = 'ml-2 block text-sm text-gray-700';

    protected string $buttonClass = 'inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700';

    protected string $errorClass = 'mt-1 text-sm text-red-600';

    protected string $formErrorsClass = 'mb-4 rounded-md border border-red-300 bg-red-50 p-4 text-sm text-red-700';

    /**
     * @param array<string, mixed> $attrs
     */
    public function formRow(Html $label, Html $input, Html $errors, array $attrs): Html
    {
        return $this->applyAttrs(Html::el('div')->class($this->rowClass)->addHtml($label . $input . $errors), $attrs);
    }

    /**
     * @param array<string, mixed> $attrs
     */
    public function formLabel(BaseControl $control, array $attrs): Html
    {
        $el = parent::formLabel($control, $attrs);
        $el->class($this->labelClass);
        return $el;
    }

    /**
     * @param (string|HtmlStringable)[] $errors
     */
    public function formErrors(array $errors): Html
    {
        if ($errors === []) {
            return Html::el();
        }
        $list = Html::el('ul');
        foreach ($errors as $error) {
            $list->addHtml(Html::el('li')->addHtml($error));
        }
        return Html::el('div')->class($this->formErrorsClass)->addHtml($list);
    }

    /**
     * @param (string|HtmlStringable)[] $errors
     */
    public function rowErrors(array $errors): Html
    {
        if ($errors === []) {
            return Html::el();
        }
        $el = Html::el('p')->class($this->errorClass);
        foreach ($errors as $i => $error) {
            if ($i > 0) {
                $el->addHtml(Html::el('br'));
            }
            $el->addHtml($error);
        }
        return $el;
    }

    /**
     * @param array<string, mixed> $attrs
     */
    protected function textInput(TextInput $control, array $attrs): Html
    {
        $el = $control->getControl();
        $el->class($this->inputClass);
        $el->class($this->invalidInputClass, $control->hasErrors());
        return $this->applyAttrs($el, $attrs);
    }

    /**
     * @param array<string, mixed> $attrs
     */
    protected function textarea(TextArea $control, array $attrs): Html
    {
        $el = $control->getControl();
        $el->class($this->inputClass);
        $el->class($this->invalidInputClass, $control->hasErrors());
        return $this->applyAttrs($el, $attrs);
    }

    /**
     * @param array<string, mixed> $attrs
     */
    protected function upload(UploadControl $control, array $attrs): Html
    {
        $el = $control->getControl();
        assert($el instanceof Html);
        $el->class($this->inputClass);
        $el->class($this->invalidInputClass, $control->hasErrors());
        return $this->applyAttrs($el, $attrs);
    }

    /**
     * @param array<string, mixed> $attrs
     */
    protected function select(SelectBox $control, array $attrs): Html
    {
        $el = $control->getControl();
        $el->class($this->inputClass);
        $el->class($this->invalidInputClass, $control->hasErrors());
        return $this->applyAttrs($el, $attrs);
    }

    /**
     * @param array<string, mixed> $attrs
     */
    protected function multiSelect(MultiSelectBox $control, array $attrs): Html
    {
        $el = $control->getControl();
        $el->class($this->inputClass);
        $el->class($this->invalidInputClass, $control->hasErrors());
        return $this->applyAttrs($el, $attrs);
    }

    /**
     * @param array<string, mixed> $attrs
     */
    protected function checkbox(Checkbox $control, array $attrs): Html
    {
        // see Bootstrap5FormTemplate::checkbox() for why getControlPart()/getLabelPart()
        // are needed instead of getControl()/getLabel()
        $input = $control->getControlPart();
        $input->class($this->checkboxInputClass);
        $input->class($this->invalidInputClass, $control->hasErrors());

        $label = $control->getLabelPart();
        $label->class($this->checkboxLabelClass);

        return Html::el('div')->class($this->checkboxWrapperClass)
            ->addHtml($this->applyAttrs($input, $attrs))
            ->addHtml($label)
        ;
    }

    /**
     * @param array<string, mixed> $attrs
     */
    protected function button(Button $control, array $attrs): Html
    {
        $el = $control->getControl();
        $el->class($this->buttonClass);
        return $this->applyAttrs($el, $attrs);
    }
}
