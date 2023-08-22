<?php

namespace Efabrica\NeoForms\Render;

use Efabrica\NeoForms\Build\NeoForm;
use Efabrica\NeoForms\Render\Template\DefaultFormTemplate;
use Latte\Engine;
use Nette\Forms\Container;
use Nette\Forms\ControlGroup;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\Button;
use Nette\Forms\Controls\Checkbox;
use Nette\Forms\Controls\HiddenField;
use Nette\Forms\Form;
use Nette\HtmlStringable;
use Nette\Localization\Translator;
use Nette\Utils\Html;
use Nette\Utils\Strings;
use RuntimeException;

class NeoFormRenderer
{
    public DefaultFormTemplate $template;
    private Translator $translator;

    public function __construct(DefaultFormTemplate $template, Translator $translator)
    {
        $this->template = $template;
        $this->translator = $translator;
    }

    /**
     * @param string|true|HtmlStringable|null $name
     */
    public function group(ControlGroup $group, $name = null): string
    {
        $body = Html::el();
        $children = $group->getOption('children');
        if (is_iterable($children)) {
            foreach ($children as $key => $child) {
                if ($child instanceof ControlGroup) {
                    $body->addHtml($this->group($child, $key));
                }
            }
        }
        foreach ($group->getControls() as $control) {
            if ($control instanceof BaseControl) {
                /** @var bool $rendered */
                $rendered = $control->getOption('rendered') ?? false;
                if ($rendered === false) {
                    $body->addHtml($this->row($control));
                }
            } elseif ($control instanceof Container) {
                $body->addHtml($this->container($control, []));
            }
        }

        if (trim($body->getHtml()) === '') {
            return '';
        }

        return $this->template->formGroup($group->getOption('label'), $body, $group->getOptions());
    }

    /**
     * @param BaseControl|Container $el
     * @param array                 $attrs []
     * @return string
     */
    public function row($el, array $attrs = []): string
    {
        if ($el instanceof Container) {
            return $this->container($el, $attrs);
        }

        /** @var bool $rendered */
        $rendered = $el->getOption('rendered') ?? false;
        if ($rendered) {
            return '';
        }

        $inputAttrs = $attrs['input'] ?? [];
        unset($attrs['input']);

        if ($attrs['readonly'] ?? false) {
            $inputAttrs['readonly'] = $attrs['readonly'];
        }

        return $this->template->formRow(
            Html::fromHtml($this->label($el, $attrs)),
            Html::fromHtml($this->input($el, $inputAttrs)),
            Html::fromHtml($this->errors($el)),
            $attrs
        );
    }

    public function input(BaseControl $el, array $attrs = []): string
    {
        /** @var Html $control */
        $control = $el->getControl();

        if ($attrs['readonly'] ?? (bool)$el->getOption('readonly')) {
            return $this->template->readonly($el);
        }

        if (is_string($attrs['placeholder'] ?? null)) {
            $attrs['placeholder'] = $this->translator->translate($attrs['placeholder']);
        }

        $description = $el->getOption('description');
        $descriptionEl = Html::el();
        if ($description instanceof HtmlStringable) {
            $descriptionEl = $description->__toString();
        } elseif (is_string($description)) {
            $descriptionEl = $this->template->description($description);
        }
        return $this->template->input($el, $attrs, $descriptionEl);
    }

    public function formStart(NeoForm $form, array $attrs = []): string
    {
        $form->fireRenderEvents();
        /** @var BaseControl $control */
        foreach ($form->getControls() as $control) {
            $control->setOption('rendered', false);
        }
        if ($attrs['readonly'] ?? $form->isReadonly()) {
            foreach ($form->getComponents(true) as $control) {
                assert($control instanceof BaseControl);
                $control->setOption('readonly', $control->getOption('readonly') ?? true);
            }
        }
        $showErrors = $attrs['formErrors'] ?? true;
        return Strings::before(
            $this->template->form(
                $form,
                $showErrors ? $this->template->formErrors($form->getOwnErrors()) : Html::el(),
                Html::fromHtml('[__BODY__]'),
                $attrs
            ),
            '[__BODY__]'
        );
    }

    public function formEnd(NeoForm $form, array $options = []): string
    {
        return Strings::after(
            $this->template->form(
                $form,
                Html::fromHtml('[__BODY__]'),
                $this->formRest($form, $options),
                $options
            ),
            '[__BODY__]'
        );
    }

    public function formRest(Form $form, array $options = []): Html
    {
        $body = Html::el();
        foreach ($form->getGroups() as $key => $group) {
            $label = null;
            $optionLabel = $group->getOption('label');
            if (is_string($optionLabel) || $optionLabel instanceof Html) {
                $label = $optionLabel;
            } elseif (is_string($key)) {
                $label = $key;
            }
            $body->addHtml($this->group($group, $label));
        }

        $buttons = [];
        foreach ($form->getComponents() as $component) {
            if ($component instanceof BaseControl) {
                /** @var bool $rendered */
                $rendered = $component->getOption('rendered') ?? false;
                if ($rendered) {
                    continue;
                }
            } elseif (!$component instanceof Container) {
                continue;
            }
            if (!$component instanceof Button) {
                $body->addHtml($this->row($component));
            } elseif ($options['buttons'] ?? true) {
                $buttons[] = $component;
            }
        }
        return $this->template->formRest($body, $buttons);
    }

    public function label(BaseControl $el, array $attrs = []): string
    {
        if ($el instanceof Button || $el instanceof HiddenField || $el instanceof Checkbox) {
            return '';
        }
        return $this->template->formLabel($el, $attrs);
    }

    public function errors($el): string
    {
        if ($el instanceof BaseControl) {
            return $this->template->rowErrors($el->getErrors());
        }
        if ($el instanceof Form) {
            return $this->template->formErrors($el->getOwnErrors());
        }
        throw new RuntimeException(get_class($el) . ' is not supported in NeoFormRenderer for rendering errors');
    }

    public function sectionStart(string $caption): string
    {
        $sep = '[__BODY__]';
        $caption = $this->translator->translate($caption);
        return Strings::before($this->template->section($caption, $sep), $sep) ?? '';
    }

    public function sectionEnd(string $caption): string
    {
        $sep = '[__BODY__]';
        $caption = $this->translator->translate($caption);
        return Strings::after($this->template->section($caption, $sep), $sep) ?? '';
    }

    public function container(Container $el, array $options): string
    {
        $rows = [];
        foreach ($el->getComponents() as $component) {
            if ($component instanceof BaseControl || $component instanceof Container) {
                $rows[] = $this->row($component, []);
            }
        }
        return implode("\n", $rows);
    }

    public static function obtainFromEngine(Engine $engine): self
    {
        $self = $engine->getProviders()['neoFormRenderer'];
        assert($self instanceof self);
        return $self;
    }
}
