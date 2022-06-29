<?php

namespace Efabrica\NeoForms;

use Efabrica\NeoForms\Render\NeoInputRenderer;
use Latte\Engine;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\Button;
use Nette\Forms\Controls\Checkbox;
use Nette\Forms\Controls\HiddenField;
use Nette\Forms\Form;
use Nette\Utils\Html;
use Nette\Utils\Strings;

class NeoFormRenderer
{
    private Engine $engine;
    private string $template;
    public NeoInputRenderer $inputRenderer;

    public function __construct(Engine $engine)
    {
        $this->engine = $engine;
        $this->template = __DIR__ . '/chroma.latte';
        $this->inputRenderer = new NeoInputRenderer($this);
    }

    public function block(string $blockName, array $attrs = []): string
    {
        return $this->engine->renderToString($this->template, $attrs, $blockName);
    }

    public function row(BaseControl $el, array $options = []): string
    {
        if ($el instanceof HiddenField) {
            return $this->block('hiddenRow', [
                'inside' => '',
                'input' => Html::fromHtml($this->inputRenderer->input($el, $options['input'] ?? [])),
                'attrs' => array_filter($options, 'is_scalar'),
                'options' => $options,
            ]);
        }

        return $this->block('row', [
            'inside' => '',
            'label' => Html::fromHtml($this->label($el, $options['label'] ?? [])),
            'input' => Html::fromHtml($this->inputRenderer->input($el, $options['input'] ?? [])),
            'errors' => Html::fromHtml($this->errors($el, $options['input'] ?? [])),
            'attrs' => array_filter($options, 'is_scalar'),
            'options' => $options,
        ]);
    }

    public function rowGroupStart(BaseControl $el, array $options = []): string
    {
        if ($el instanceof HiddenField) {
            return '';
        }
        if ($el instanceof BaseControl) {
            $inside = uniqid();
            return Strings::before($this->block('row', [
                'inside' => $inside,
                'label' => '',
                'input' => '',
                'errors' => '',
                'attrs' => array_filter($options, 'is_scalar'),
                'options' => $options,
            ]), $inside);
        }
        throw new \RuntimeException(get_class($el) . " is not yet supported in NeoFormRenderer");
    }

    public function rowGroupEnd($el, array $options = []): string
    {
        if ($el instanceof HiddenField) {
            return '';
        }
        if ($el instanceof BaseControl) {
            $inside = uniqid();
            return Strings::after($this->block('row', [
                'inside' => $inside,
                'label' => '',
                'input' => '',
                'errors' => '',
                'attrs' => array_filter($options, 'is_scalar'),
                'options' => $options,
            ]), $inside);
        }
        throw new \RuntimeException(get_class($el) . " is not yet supported in NeoFormRenderer");
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
            'formErrors' => $options['formErrors'] ?? true,
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
            'formErrors' => $options['formErrors'] ?? true,
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

    public function errors($el, array $options): string
    {
        if ($el instanceof BaseControl) {
            return $this->block('errors', [
                'errors' => $el->getErrors(),
                'options' => $options,
            ]);
        } elseif ($el instanceof Form) {
            return $this->block('formErrors', [
                'errors' => $el->getOwnErrors(),
                'options' => $options,
            ]);
        }
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
