<?php

namespace Efabrica\NeoForms\Render\Template;

use Efabrica\NeoForms\Build\NeoForm;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\Button;
use Nette\Forms\Controls\Checkbox;
use Nette\Forms\Controls\CheckboxList;
use Nette\Forms\Controls\ChoiceControl;
use Nette\Forms\Controls\HiddenField;
use Nette\Forms\Controls\MultiChoiceControl;
use Nette\Forms\Controls\MultiSelectBox;
use Nette\Forms\Controls\RadioList;
use Nette\Forms\Controls\SelectBox;
use Nette\Forms\Controls\TextArea;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Controls\UploadControl;
use Nette\HtmlStringable;
use Nette\Localization\Translator;
use Nette\Utils\Html;
use RadekDostal\NetteComponents\DateTimePicker\AbstractDateTimePicker;

class DefaultFormTemplate
{
    public function form(NeoForm $form, Html $body, array $attrs): Html
    {
        return (clone $form->getElementPrototype())->addAttributes($attrs)->addHtml($body);
    }

    public function formRow(Html $label, Html $input, Html $errors, array $attrs = []): Html
    {
        return Html::el('div', $attrs)->addHtml($label . $input . $errors);
    }

    /**
     * @param string|HtmlStringable $label
     */
    public function formGroup($label, Html $body, array $attrs): Html
    {
        $legendAttrs = $attrs['legend'] ?? [];
        unset($attrs['legend']);

        if ($label !== null) {
            $el = Html::el('fieldset', $attrs);
            if (is_string($label)) {
                $el->addHtml(Html::el('legend', $legendAttrs)->addHtml($label));
            } else {
                $el->addHtml($label);
            }
        } else {
            $el = Html::el();
        }
        return $el->addHtml($body);
    }

    public function formRest(Html $body, array $buttons): Html
    {
        $el = Html::el();
        $el->addHtml($body);
        foreach ($buttons as $button) {
            $el->addHtml($button);
        }
        return $el;
    }

    /**
     * @param (string|HtmlStringable)[] $errors
     * @return Html
     */
    public function formErrors(array $errors): Html
    {
        $el = Html::el('ul');
        foreach ($errors as $error) {
            $el->addHtml(Html::el('li')->addHtml($error));
        }
        return $el;
    }

    public function rowErrors(array $errors): Html
    {
        return $this->formErrors($errors);
    }

    public function formLabel(BaseControl $control, array $attrs): Html
    {
        $el = $control->getLabel();
        if ($control->isRequired()) {
            $el->class('required', true);
        }
        $info = $control->getOption('info');
        if (is_string($info) && trim($info) !== '') {
            $el->addHtml(Html::el('br'));
            $el->addHtml(Html::el('small', $info));
        }
        return $el;
    }

    public function description(string $description): Html
    {
        return Html::el('div')->class('form-text')->setHtml($description);
    }

    public function input(BaseControl $control, array $attrs, Html $description): Html
    {
        return Html::el()->addHtml($this->control($control, $attrs) . $description);
    }

    protected function control(BaseControl $control, array $attrs): Html
    {
        if ($control instanceof Checkbox) {
            return $this->checkbox($control, $attrs);
        }
        if ($control instanceof AbstractDateTimePicker) {
            return $this->datepicker($control, $attrs);
        }
        if ($control instanceof SelectBox) {
            return $this->select($control, $attrs);
        }
        if ($control instanceof MultiSelectBox) {
            return $this->multiSelect($control, $attrs);
        }
        if ($control instanceof Button) {
            return $this->button($control, $attrs);
        }
        if ($control instanceof TextArea) {
            return $this->textarea($control, $attrs);
        }
        if ($control instanceof HiddenField) {
            return $this->hidden($control, $attrs);
        }
        if ($control instanceof UploadControl) {
            return $this->upload($control, $attrs);
        }
        if ($control instanceof RadioList) {
            return $this->radio($control, $attrs);
        }
        if ($control instanceof CheckboxList) {
            return $this->checkboxList($control, $attrs);
        }
        if ($control instanceof TextInput) {
            return $this->textInput($control, $attrs);
        }
        return $control->getControl();

    }

    protected function checkbox(Checkbox $control, array $attrs): Html
    {
        return $control->getControl()->addAttributes($attrs);
    }

    protected function datepicker(AbstractDateTimePicker $control, array $attrs): Html
    {
        return $control->getControl()->addAttributes($attrs);
    }

    protected function select(SelectBox $control, array $attrs): Html
    {
        return $control->getControl()->addAttributes($attrs);
    }

    protected function multiSelect(MultiSelectBox $control, array $attrs): Html
    {
        return $control->getControl()->addAttributes($attrs);
    }

    protected function button(Button $control, array $attrs): Html
    {
        return $control->getControl()->addAttributes($attrs);
    }

    protected function textarea(TextArea $control, array $attrs): Html
    {
        return $control->getControl()->addAttributes($attrs);
    }

    protected function hidden(HiddenField $control, array $attrs): Html
    {
        return $control->getControl()->addAttributes($attrs);
    }

    protected function upload(UploadControl $control, array $attrs): Html
    {
        return $control->getControl()->addAttributes($attrs);
    }

    protected function radio(RadioList $control, array $attrs): Html
    {
        return $control->getControl()->addAttributes($attrs);
    }

    protected function checkboxList(CheckboxList $control, array $attrs): Html
    {
        return $control->getControl()->addAttributes($attrs);
    }

    protected function textInput(TextInput $control, array $attrs): Html
    {
        return $control->getControl()->addAttributes($attrs);
    }

    public function readonly(BaseControl $control): Html
    {
        $value = $control->getValue();
        if ($value === null) {
            return Html::fromText('—');
        }
        if ($control instanceof TextArea) {
            return Html::el('pre', $control->getValue());
        }
        if ($control instanceof Checkbox || is_bool($value)) {
            return Html::fromText($value ? '✓' : '✕');
        }
        if ($control instanceof ChoiceControl) {
            return Html::fromText($control->getSelectedItem());
        }
        if ($control instanceof MultiChoiceControl) {
            return Html::fromText(implode(', ', $control->getSelectedItems()));
        }
        if ($control instanceof TextInput || is_string($value)) {
            return Html::fromText($value);
        }
        return Html::fromText('(?)');
    }

    public function section(string $caption, string $inside): Html
    {
        return Html::el('fieldset')->addHtml(Html::el('legend', $caption))->addHtml($inside);
    }
}
