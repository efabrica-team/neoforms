<?php

namespace Efabrica\NeoForms\Render\Template;

use Efabrica\NeoForms\Build\NeoForm;
use Efabrica\NeoForms\Control\ToggleSwitch;
use Efabrica\NeoForms\Render\NeoFormRenderer;
use Generator;
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
use Nette\Forms\Controls\TextBase;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Controls\UploadControl;
use Nette\HtmlStringable;
use Nette\Utils\Html;
use RadekDostal\NetteComponents\DateTimePicker\AbstractDateTimePicker;

class NeoFormTemplate
{
    public function form(NeoFormRenderer $renderer, NeoForm $form, Html $errors, array $attrs): Generator
    {
        $renderRest = $attrs['rest'] ?? true;
        $el = clone $form->getElementPrototype();
        $el->addHtml($errors)->addHtml(yield)->addHtml($renderRest ? $renderer->formRest($form) : '');
        return $this->applyAttrs($el, $attrs);
    }

    public function formRow(Html $label, Html $input, Html $errors, array $attrs): Html
    {
        return $this->applyAttrs(Html::el('div')->addHtml($label . $input . $errors), $attrs);
    }

    /**
     * @param string|HtmlStringable|null $label
     */
    public function formGroup($label, Html $body, array $attrs): Html
    {
        $legendAttrs = $attrs['legend'] ?? [];
        unset($attrs['legend']);

        if ($label !== null) {
            $el = Html::el('fieldset');
            if (is_string($label)) {
                $el->addHtml($this->applyAttrs(Html::el('legend')->addHtml($label), $legendAttrs));
            } else {
                $el->addHtml($label);
            }
        } else {
            $el = Html::el();
        }
        return $this->applyAttrs($el->addHtml($body), $attrs);
    }

    /**
     * @param Html   $body
     * @param Html[] $groups
     * @param array  $buttons
     * @return Html
     */
    public function formRest(Html $body, array $groups, array $buttons): Html
    {
        $el = Html::el();
        $el->addHtml($body);
        foreach ($groups as $group) {
            $el->addHtml($group);
        }
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
        if (!$el instanceof Html) {
            return Html::el();
        }
        $el->class('required', $control->isRequired());
        $info = $control->getOption('info');
        if (is_string($info) && trim($info) !== '') {
            $el->addHtml(Html::el('br'));
            $el->addHtml(Html::el('small', $info));
        }
        return $this->applyAttrs($el, $attrs);
    }

    public function description(string $description): Html
    {
        return Html::el('div')->class('form-text')->setHtml($description);
    }

    public function formInput(BaseControl $control, array $attrs, Html $description): Html
    {
        return Html::el()
            ->addHtml($this->control($control, $attrs))
            ->addHtml($description)
        ;
    }

    protected function control(BaseControl $control, array $attrs): Html
    {
        $attrs += $control->getOptions();
        if ($control instanceof ToggleSwitch) {
            return $this->toggleSwitch($control, $attrs + $control->getOptions());
        }
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
        /** @var TextBase $control */
        return $control->getControl();
    }

    protected function checkbox(Checkbox $control, array $attrs): Html
    {
        return $this->applyAttrs($control->getControl(), $attrs);
    }

    protected function datepicker(AbstractDateTimePicker $control, array $attrs): Html
    {
        return $this->applyAttrs($control->getControl(), $attrs);
    }

    protected function select(SelectBox $control, array $attrs): Html
    {
        return $this->applyAttrs($control->getControl(), $attrs);
    }

    protected function multiSelect(MultiSelectBox $control, array $attrs): Html
    {
        return $this->applyAttrs($control->getControl(), $attrs);
    }

    protected function button(Button $control, array $attrs): Html
    {
        return $this->applyAttrs($control->getControl(), $attrs);
    }

    protected function textarea(TextArea $control, array $attrs): Html
    {
        return $this->applyAttrs($control->getControl(), $attrs);
    }

    protected function hidden(HiddenField $control, array $attrs): Html
    {
        return $this->applyAttrs($control->getControl(), $attrs);
    }

    protected function upload(UploadControl $control, array $attrs): Html
    {
        /** @var TextBase $control */
        return $this->applyAttrs($control->getControl(), $attrs);
    }

    protected function radio(RadioList $control, array $attrs): Html
    {
        return $this->applyAttrs($control->getControl(), $attrs);
    }

    protected function checkboxList(CheckboxList $control, array $attrs): Html
    {
        return $this->applyAttrs($control->getControl(), $attrs);
    }

    protected function textInput(TextInput $control, array $attrs): Html
    {
        return $this->applyAttrs($control->getControl(), $attrs);
    }

    public function readonly(BaseControl $control): Html
    {
        $value = $control->getValue();
        if ($value === null) {
            return Html::fromText('—');
        }
        if ($control instanceof TextArea && is_string($value)) {
            return Html::el('pre', $value);
        }
        if ($control instanceof Checkbox || is_bool($value)) {
            return Html::fromText((is_scalar($value) && (bool)$value) ? '✓' : '✕');
        }
        if ($control instanceof ChoiceControl) {
            $selectedItem = $control->getSelectedItem();
            if (is_scalar($selectedItem)) {
                return Html::fromText((string)$selectedItem);
            }
        }
        if ($control instanceof MultiChoiceControl) {
            return Html::fromText(implode(', ', $control->getSelectedItems()));
        }
        if (is_string($value)) {
            return Html::fromText($value);
        }
        return Html::fromText('(?)');
    }

    public function applyAttrs(Html $el, array $attrs): Html
    {
        foreach ($attrs as $key => $value) {
            if (!is_scalar($value)) {
                continue;
            }
            if ($key[0] === '+') {
                $el->appendAttribute(substr($key, 1), $value);
            } elseif ($value === false) {
                $el->removeAttribute($key);
            } else {
                $el->setAttribute($key, $value);
            }
        }
        return $el;
    }

    private function toggleSwitch(ToggleSwitch $control, array $params): Html
    {
        return $this->checkbox($control, $params);
    }
}
