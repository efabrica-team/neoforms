<?php

namespace Efabrica\NeoForms;

use Efabrica\NeoForms\Control\ToggleSwitch;
use Efabrica\Nette\Forms\Rte\RteControl;
use Latte\Engine;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\Button;
use Nette\Forms\Controls\Checkbox;
use Nette\Forms\Controls\HiddenField;
use Nette\Forms\Controls\SelectBox;
use Nette\Forms\Controls\TextArea;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;
use Nette\Utils\Html;
use Nette\Utils\Strings;
use RadekDostal\NetteComponents\DateTimePicker\AbstractDateTimePicker;
use RadekDostal\NetteComponents\DateTimePicker\DateTimePicker;
use RadekDostal\NetteComponents\DateTimePicker\TbDateTimePicker;

class NeoFormRenderer
{
    private Engine $engine;
    private string $template;

    public function __construct(Engine $engine)
    {
        $this->engine = $engine;
        $this->template = __DIR__ . '/chroma.latte';
    }

    protected function block(string $blockName, array $attrs = []): string
    {
        return $this->engine->renderToString($this->template, $attrs, $blockName);
    }

    public function row($el, array $options = []): string
    {
        if ($el instanceof HiddenField) {
            return $this->block('hiddenRow', [
                'input' => Html::fromHtml($this->input($el, $options['input'] ?? [])),
                'attrs' => array_filter($options, 'is_scalar'),
                'options' => $options,
            ]);
        }
        if ($el instanceof BaseControl) {
            return $this->block('row', [
                'label' => Html::fromHtml($this->label($el, $options['label'] ?? [])),
                'input' => Html::fromHtml($this->input($el, $options['input'] ?? [])),
                'attrs' => array_filter($options, 'is_scalar'),
                'options' => $options,
            ]);
        }
        if ($el instanceof Form) {
            return $this->form($el);
        }
        throw new \RuntimeException(get_class($el) . " is not yet supported in NeoFormRenderer");
    }

    public function form(Form $form): string
    {
        $s = $this->formStart($form);
        foreach ($form->getComponents() as $component) {
            $s .= $this->row($component);
        }
        return $s . $this->formEnd($form);
    }

    public function formStart(Form $form, array $options): string
    {
        foreach ($form->getControls() as $control) {
            $control->setOption('rendered', false);
        }
        bdump($options);
        $inside = uniqid();
        return Strings::before($this->block('form', [
            'form' => $form,
            'attrs' => $form->getElementPrototype()->attrs,
            'inside' => $inside,
            'errors' => $form->getOwnErrors(),
            'options' => $options,
            'renderRest' => false,
        ]), $inside);
    }

    public function formEnd(Form $form, $options): string
    {
        $inside = uniqid();
        return Strings::after($this->block('form', [
            'form' => $form,
            'attrs' => $form->getElementPrototype()->attrs,
            'inside' => $inside,
            'errors' => $form->getOwnErrors(),
            'options' => $options,
            'renderRest' => $options['rest'] ?? true,
        ]), $inside);
    }

    public function formRest(Form $form, array $options = [])
    {
        $components = array_filter(iterator_to_array($form->getComponents()),
            fn($a) => $a instanceof BaseControl && !$a->getOption('rendered'));
        $rest = array_filter($components, fn($a) => !$a instanceof Button);
        $buttons = ($options['buttons'] ?? true) ? array_filter($components, fn($a) => $a instanceof Button) : [];
        return $this->block('formRest', [
            'renderer' => $this,
            'form' => $form,
            'rest' => $rest,
            'buttons' => $buttons,
        ]);
    }

    public function label(BaseControl $el, array $options = []): string
    {
        if ($el instanceof Checkbox || $el instanceof Button || $el instanceof HiddenField) {
            return '';
        }
        return $this->block('label', [
            'for' => $el->getHtmlId(),
            'caption' => $el->getCaption(),
            'info' => $el->getOption('info'),
            'errors' => $el->getErrors(),
            'attrs' => array_filter($options, 'is_scalar'),
        ]);
    }

    public function formErrors(Form $el)
    {
        return $this->block('errors', [
            'errors' => $el->getOwnErrors(),
        ]);
    }

    public function input(BaseControl $el, array $options = []): string
    {
        if ($el instanceof Checkbox) {
            return $this->checkbox($el, $options);
        }

        $attrs = $el->getControl()->attrs;
        unset($attrs['data-nette-rules']);
        $attrs += array_filter($options, 'is_scalar');

        $s = '';
        if ($el instanceof AbstractDateTimePicker) {
            $s .= $this->datepicker($el, $attrs, $options);
        } elseif ($el instanceof TextInput) {
            $s .= $this->textInput($el, $attrs, $options);
        } elseif ($el instanceof SelectBox) {
            $s .= $this->selectBox($el, $attrs, $options);
        } elseif ($el instanceof Button) {
            $s .= $this->button($el, $attrs, $options);
        } elseif ($el instanceof TextArea || $el instanceof RteControl) {
            $s .= $this->textarea($el, $attrs, $options);
        } elseif ($el instanceof HiddenField) {
            $s .= $this->hidden($el, $attrs, $options);
        } else {
            throw new \RuntimeException(get_class($el) . " is not yet supported in NeoFormRenderer");
        }

        return $s . $this->description($el, $options);
    }

    public function description(BaseControl $el, array $options)
    {
        return $this->block('description', [
            'el' => $el,
            'description' => $el->getOption('description'),
            'options' => $options,
        ]);
    }

    public function textInput(TextInput $el, array $attrs, array $options): string
    {
        return $this->block('inputText', [
            'attrs' => $attrs,
            'options' => $el->getOptions() + $options,
            'description' => $el->getOption('description'),
        ]);
    }

    public function selectBox(SelectBox $el, array $attrs, array $options): string
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

    public function sectionStart(string $caption): string
    {
        $sep = uniqid();
        return Strings::before($this->block('section', [
            'inside' => $sep,
            'caption' => $caption,
        ]), $sep);
    }

    public function sectionEnd(string $caption): string
    {
        $sep = uniqid();
        return Strings::after($this->block('section', [
            'inside' => $sep,
            'caption' => $caption,
        ]), $sep);
    }

    public function hidden(HiddenField $el, array $attrs, array $options)
    {
        return $this->block('hidden', [
            'attrs' => $attrs,
            'options' => $options,
            'errors' => $el->getErrors(),
        ]);
    }
}
