<?php

namespace Efabrica\NeoForms\Render;

use Efabrica\NeoForms\Control\ToggleSwitch;
use Efabrica\NeoForms\NeoFormRenderer;
use Efabrica\Nette\Forms\Rte\RteControl;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\Button;
use Nette\Forms\Controls\Checkbox;
use Nette\Forms\Controls\HiddenField;
use Nette\Forms\Controls\MultiSelectBox;
use Nette\Forms\Controls\SelectBox;
use Nette\Forms\Controls\TextArea;
use Nette\Forms\Controls\TextInput;
use Nette\Utils\Html;
use RadekDostal\NetteComponents\DateTimePicker\AbstractDateTimePicker;
use RadekDostal\NetteComponents\DateTimePicker\DateTimePicker;
use RadekDostal\NetteComponents\DateTimePicker\TbDateTimePicker;
use RuntimeException;

class NeoInputRenderer
{
    private NeoFormRenderer $renderer;

    public function __construct(NeoFormRenderer $renderer)
    {
        $this->renderer = $renderer;
    }

    private function block(string $blockName, array $attrs): string
    {
        return $this->renderer->block($blockName, $attrs);
    }

    public function input(BaseControl $el, array $options = []): string
    {
        if ($el instanceof Checkbox) {
            return $this->checkbox($el, $options);
        }

        /** @var Html $control */
        $control = $el->getControl();
        $attrs = $control->attrs;
        unset($attrs['data-nette-rules']);
        $attrs += array_filter($options, 'is_scalar');

        $s = '';
        if ($el instanceof AbstractDateTimePicker) {
            $s .= $this->datepicker($el, $attrs, $options);
        } elseif ($el instanceof TextInput) {
            $s .= $this->textInput($el, $attrs, $options);
        } elseif ($el instanceof SelectBox || $el instanceof MultiSelectBox) {
            $s .= $this->selectBox($el, $attrs, $options);
        } elseif ($el instanceof Button) {
            $s .= $this->button($el, $attrs, $options);
        } elseif ($el instanceof TextArea || $el instanceof RteControl) {
            $s .= $this->textarea($el, $attrs, $options);
        } elseif ($el instanceof HiddenField) {
            $s .= $this->hidden($el, $attrs, $options);
        } else {
            throw new RuntimeException(get_class($el) . ' is not yet supported in NeoFormRenderer');
        }

        return $s . $this->description($el, $options);
    }

    public function textInput(TextInput $el, array $attrs, array $options): string
    {
        return $this->block('inputText', [
            'attrs' => $attrs,
            'options' => $el->getOptions() + $options,
            'description' => $el->getOption('description'),
        ]);
    }

    public function selectBox($el, array $attrs, array $options): string
    {
        return $this->block('selectBox', [
            'attrs' => $attrs,
            'options' => $el->getOptions() + $options,
            'items' => $el->getItems(),
            'value' => $el->getValue(),
        ]);
    }

    public function button(Button $el, array $attrs, array $options): string
    {
        return $this->block('submitButton', [
            'attrs' => $attrs,
            'options' => $el->getOptions() + $options,
            'icon' => $el->getOption('icon', 'done'),
            'caption' => $el->getCaption(),
        ]);
    }

    public function checkbox(Checkbox $checkbox, array $options): string
    {

        return $this->block($checkbox instanceof ToggleSwitch ? 'toggleSwitch' : 'checkbox', [
            'caption' => $checkbox->getCaption(),
            'info' => $checkbox->getOption('info'),
            'labelAttrs' => array_filter($checkbox->getLabelPart()->attrs, 'is_scalar'),
            'inputAttrs' => array_filter($checkbox->getControlPart()->attrs, 'is_scalar'),
            'options' => $checkbox->getOptions() + $options,
        ]);
    }

    public function textarea(BaseControl $el, array $attrs, array $options): string
    {
        return $this->block('textarea', [
            'value' => $el->getValue(),
            'attrs' => $attrs,
            'options' => $el->getOptions() + $options,
        ]);
    }

    public function datepicker(AbstractDateTimePicker $el, array $attrs, array $options): string
    {
        return $this->block('datepicker', [
            'attrs' => $attrs,
            'options' => $el->getOptions() + $options,
            'isTime' => $el instanceof DateTimePicker || $el instanceof TbDateTimePicker,
        ]);
    }

    public function hidden(HiddenField $el, array $attrs, array $options): string
    {
        return $this->block('hidden', [
            'attrs' => $attrs,
            'options' => $options,
            'errors' => $el->getErrors(),
        ]);
    }

    public function description(BaseControl $el, array $options): string
    {
        return $this->block('description', [
            'el' => $el,
            'description' => $el->getOption('description'),
            'options' => $options,
        ]);
    }
}
