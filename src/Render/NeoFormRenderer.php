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

    public function formGroup(ControlGroup $group): Html
    {
        $body = Html::el();
        $children = $group->getOption('children');
        if (is_iterable($children)) {
            foreach ($children as $child) {
                if ($child instanceof ControlGroup) {
                    $body->addHtml($this->formGroup($child));
                }
            }
        }
        foreach ($group->getControls() as $control) {
            if ($control instanceof BaseControl) {
                /** @var bool $rendered */
                $rendered = $control->getOption('rendered') ?? false;
                if ($rendered === false) {
                    $body->addHtml($this->formRow($control));
                }
            } elseif ($control instanceof Container) {
                $body->addHtml($this->container($control, []));
            }
        }

        if (trim($body->getHtml()) === '') {
            return Html::el();
        }

        $label = $group->getOption('label');
        if (is_string($label)) {
            $label = $this->translator->translate($label);
        }

        return $this->template->formGroup($label, $body, $group->getOption('attrs') ?? []);
    }

    /**
     * @param BaseControl|Container $el
     * @param array                 $attrs []
     * @return string
     */
    public function formRow($el, array $attrs = []): Html
    {
        if ($el instanceof Container) {
            return $this->container($el);
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
            Html::fromHtml($this->formLabel($el, $attrs)),
            Html::fromHtml($this->formInput($el, $inputAttrs)),
            Html::fromHtml($this->formErrors($el)),
            $attrs
        );
    }

    public function formInput(BaseControl $el, array $attrs = []): Html
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
        if (is_string($description)) {
            $descriptionEl = $this->template->description($description);
        } elseif ($description instanceof Html) {
            $descriptionEl = $description;
        }
        return $this->template->input($el, $attrs, $descriptionEl ?? Html::el());
    }

    public function formStart(NeoForm $form, array $attrs = []): Html
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
        return Html::fromHtml(Strings::before(
            $this->template->form(
                $form,
                Html::fromHtml($showErrors ? $this->template->formErrors($form->getOwnErrors()) : Html::el())->addHtml('[__BODY__]'),
                $attrs
            ),
            '[__BODY__]'
        ));
    }

    public function formEnd(NeoForm $form, array $attrs = []): string
    {
        return Html::fromHtml(Strings::after(
            $this->template->form(
                $form,
                Html::fromHtml('[__BODY__]')->addHtml($this->formRest($form, $attrs)),
                $attrs
            ),
            '[__BODY__]'
        ));
    }

    public function formRest(Form $form, array $options = []): Html
    {
        $body = Html::el();
        foreach ($form->getGroups() as $key => $group) {
            $group->setOption('label', $group->getOption('label') ?? $key);
            $body->addHtml($this->formGroup($group));
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
                $body->addHtml($this->formRow($component));
            } elseif ($options['buttons'] ?? true) {
                $buttons[] = $this->formRow($component);
            }
        }
        bdump($body);
        return $this->template->formRest($body, $buttons);
    }

    public function formLabel(BaseControl $el, array $attrs = []): Html
    {
        if ($el instanceof Button || $el instanceof HiddenField || $el instanceof Checkbox) {
            return Html::el();
        }
        if (is_string($el->getOption('info'))) {
            $el->setOption('info', $this->translator->translate($el->getOption('info')));
        }
        return $this->template->formLabel($el, $attrs);
    }

    public function formErrors($el): Html
    {
        if ($el instanceof BaseControl) {
            return $this->template->rowErrors($el->getErrors());
        }
        if ($el instanceof Form) {
            return $this->template->formErrors($el->getOwnErrors());
        }
        throw new RuntimeException(get_class($el) . ' is not supported in NeoFormRenderer for rendering errors');
    }

    public function sectionStart(string $caption): Html
    {
        $sep = '[__BODY__]';
        $caption = $this->translator->translate($caption);
        return Html::fromHtml(Strings::before($this->template->section($caption, $sep), $sep) ?? '');
    }

    public function sectionEnd(string $caption): Html
    {
        $sep = '[__BODY__]';
        $caption = $this->translator->translate($caption);
        return Html::fromHtml(Strings::after($this->template->section($caption, $sep), $sep) ?? '');
    }

    public function container(Container $el): Html
    {
        $rows = Html::el();
        foreach ($el->getComponents() as $component) {
            if ($component instanceof BaseControl || $component instanceof Container) {
                $rows->addHtml($this->formRow($component));
            }
        }
        return $rows;
    }

    public static function obtainFromEngine(Engine $engine): self
    {
        $self = $engine->getProviders()['neoFormRenderer'];
        assert($self instanceof self);
        return $self;
    }
}
