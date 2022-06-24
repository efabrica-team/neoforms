<?php

namespace Efabrica\NeoForms;

use Latte\Engine;
use Nette\Forms\ControlGroup;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\Button;
use Nette\Forms\Controls\Checkbox;
use Nette\Forms\Controls\SelectBox;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;
use Nette\Utils\Html;
use Nette\Utils\Strings;

class NeoFormRenderer
{
    private Engine $engine;
    private string $template;

    public function __construct(Engine $engine, ?string $template = null)
    {
        $this->engine = $engine;
        $this->template = $template ?? __DIR__ . '/chroma.latte';
    }

    protected function block(string $blockName, array $attrs = []): string
    {
        return $this->engine->renderToString($this->template, $attrs, $blockName);
    }

    public function row($el, array $options = []): string
    {
        if ($el instanceof BaseControl) {
            return $this->block('row', [
                'label' => Html::fromHtml($this->label($el, $options['input'] ?? [])),
                'input' => Html::fromHtml($this->input($el, $options['label'] ?? [])),
                'errors' => Html::fromHtml($this->errors($el)),
                'attrs' => array_filter($options, 'is_scalar'),
            ]);
        }
        if ($el instanceof Form) {
            return $this->form($el);
        }
        throw new \RuntimeException(get_class($el)."is not yet supported in NeoFormRenderer");
    }

    public function form(Form $form): string
    {
        $s = $this->formStart($form);
        foreach ($form->getComponents() as $component) {
            $s .= $this->row($component);
        }
        return $s . $this->formEnd($form);
    }

    public function formStart(Form $form): string
    {
        $inside = uniqid();
        return Strings::before($this->block('form', [
            'form' => $form,
            'attrs' => $form->getElementPrototype()->attrs,
            'inside' => $inside,
        ]), $inside);
    }

    public function formEnd(Form $form): string
    {
        $inside = uniqid();
        return Strings::after($this->block('form', [
            'form' => $form,
            'attrs' => $form->getElementPrototype()->attrs,
            'inside' => $inside,
        ]), $inside);
    }

    public function formRest(Form $form, bool $withButtons = false)
    {
        $components = array_filter(iterator_to_array($form->getComponents()), fn($a) => $a instanceof BaseControl && !$a->getOption('rendered'));
        $rest = array_filter($components, fn($a) => !$a instanceof Button);
        $buttons = array_diff($components, $rest);
        return $this->block('formButtons', [
            'renderer' => $this,
            'form' => $form,
            'rest' => $rest,
            'buttons' => $buttons,
        ]);
    }

    public function label(BaseControl $el, array $options = []): string
    {
        if ($el instanceof Checkbox) {
            return '';
        }
        if ($el instanceof Button) {
            return '';
        }
        return $this->block('label', [
            'for' => $el->getHtmlId(),
            'caption' => $el->getCaption(),
            'info' => $el->getOption('info'),
            'attrs' => array_filter($options, 'is_scalar')
        ]);
    }

    public function errors(BaseControl $el)
    {
        return $this->block('errors', [
            'errors' => $el->getErrors(),
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

        if ($el instanceof TextInput) {
            return $this->textInput($el, $attrs, $options);
        }
        if ($el instanceof SelectBox) {
            return $this->selectBox($el, $attrs, $options);
        }
        if ($el instanceof Button) {
            return $this->button($el, $attrs, $options);
        }
    }

    protected function textInput(TextInput $el, array $attrs, array $options): string
    {
        return $this->block('inputText', [
            'attrs' => $attrs,
            'options' => $el->getOptions() + $options,
            'desc' => $el->getOption('desc'),
        ]);
    }

    protected function selectBox(SelectBox $el, array $attrs, array $options): string
    {
        return $this->block('selectBox', [
            'attrs' => $attrs,
            'options' => $el->getOptions() + $options,
            'items' => $el->getItems(),
            'desc' => $el->getOption('desc'),
        ]);
    }

    public function button(Button $el, array $attrs, array $options): string
    {
        return $this->block('submitButton', [
            'attrs' => $attrs,
            'options' => $el->getOptions() + $options,
            'icon' => $el->getOption('icon', 'done'),
            'caption' => $el->getCaption(),
            'desc' => $el->getOption('desc'),
        ]);
    }

    public function checkbox(Checkbox $checkbox, array $options): string
    {
        return $this->block('checkbox', [
            'id' => $checkbox->getHtmlId(),
            'name' => $checkbox->getHtmlName(),
            'caption' => $checkbox->getCaption(),
            'checked' => $checkbox->getValue(),
            'info' => $checkbox->getOption('info'),
            'desc' => $checkbox->getOption('desc'),
            'attrs' => array_filter($options, 'is_scalar'),
            'options' => $checkbox->getOptions() + $options,
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
}
