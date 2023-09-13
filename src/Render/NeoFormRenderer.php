<?php

namespace Efabrica\NeoForms\Render;

use Efabrica\NeoForms\Build\NeoContainer;
use Efabrica\NeoForms\Build\NeoForm;
use Efabrica\NeoForms\Control\FormCollection;
use Efabrica\NeoForms\Render\Template\NeoFormTemplate;
use Generator;
use Nette\Bridges\ApplicationLatte\Template;
use Nette\ComponentModel\Component;
use Nette\Forms\Container;
use Nette\Forms\ControlGroup;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\Button;
use Nette\Forms\Controls\Checkbox;
use Nette\Forms\Controls\HiddenField;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;
use Nette\Localization\Translator;
use Nette\Utils\Html;
use RuntimeException;

class NeoFormRenderer
{
    public NeoFormTemplate $defaultTemplate;

    private Translator $translator;

    public function __construct(NeoFormTemplate $template, Translator $translator)
    {
        $this->defaultTemplate = $template;
        $this->translator = $translator;
    }

    protected function template(?Form $form): NeoFormTemplate
    {
        return ($form instanceof NeoForm ? $form->getTemplate() : null) ?? $this->defaultTemplate;
    }

    public function form(NeoForm $form, array $attrs = []): Generator
    {
        $form->fireRenderEvents();

        $readonly = $attrs['readonly'] ?? $form->isReadonly();
        /** @var BaseControl $control */
        foreach ($form->getComponents(true, BaseControl::class) as $control) {
            $control->setOption('rendered', false);
            if ($readonly) {
                $control->setOption('readonly', $control->getOption('readonly') ?? true);
            }
        }

        $showErrors = $attrs['formErrors'] ?? true;
        $formErrors = $showErrors ? $this->template($form)->formErrors($form->getOwnErrors()) : Html::el();
        unset($attrs['formErrors']);

        $generator = $this->template($form)->form($this, $form, $formErrors, $attrs);
        $generator->send(yield);
        return $generator->getReturn();
    }

    public function formGroup(NeoForm $form, ControlGroup $group): Html
    {
        $body = Html::el();
        $children = $group->getOption('children');
        if (is_iterable($children)) {
            foreach ($children as $child) {
                if ($child instanceof ControlGroup) {
                    $body->addHtml($this->formGroup($form, $child));
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
                $body->addHtml($this->container($control));
            }
        }

        if (trim($body->getHtml()) === '') {
            return Html::el();
        }

        $label = $group->getOption('label');
        if (is_string($label)) {
            $label = $this->translator->translate($label);
        }
        if ($label !== null && !is_string($label) && !$label instanceof Html) {
            $label = null;
        }

        $attrs = $group->getOption('attrs') ?? [];
        return $this->template($form)->formGroup($label, $body, is_array($attrs) ? $attrs : []);
    }

    /**
     * @param BaseControl|Container $el
     */
    public function formRow($el, array $attrs = []): Html
    {
        if ($el instanceof Container) {
            return $this->container($el);
        }

        /** @var bool $rendered */
        $rendered = $el->getOption('rendered') ?? false;
        if ($rendered) {
            return Html::el();
        }

        $inputAttrs = $attrs['input'] ?? [];
        unset($attrs['input']);

        if ($attrs['readonly'] ?? false) {
            $inputAttrs['readonly'] = $attrs['readonly'];
        }

        if ($el instanceof HiddenField) {
            return $this->template($el->getForm())->formInput($el, $inputAttrs, Html::el());
        }

        return $this->template($el->getForm())->formRow(
            Html::fromHtml($this->formLabel($el, $attrs)),
            Html::fromHtml($this->formInput($el, $inputAttrs)),
            Html::fromHtml($this->formErrors($el)),
            $attrs
        );
    }

    public function formInput(BaseControl $el, array $attrs = []): Html
    {
        if ($attrs['readonly'] ?? (bool)$el->getOption('readonly')) {
            return $this->template($el->getForm())->readonly($el);
        }

        if (is_string($attrs['placeholder'] ?? null)) {
            $attrs['placeholder'] = $this->translator->translate($attrs['placeholder']);
        }

        /** Hotfix as SubmitButton does not have the correct type */
        if ($el instanceof SubmitButton && !$el instanceof \Efabrica\NeoForms\Control\SubmitButton) {
            $el->setOption('type', null);
        }

        $description = $el->getOption('description');
        if (is_string($description)) {
            $descriptionEl = $this->template($el->getForm())->description($description);
        } elseif ($description instanceof Html) {
            $descriptionEl = $description;
        }
        return $this->template($el->getForm())->formInput($el, $attrs, $descriptionEl ?? Html::el());
    }

    public function formRest(NeoForm $form, array $options = []): Html
    {
        $body = Html::el();
        foreach ($form->getGroups() as $key => $group) {
            $group->setOption('label', $group->getOption('label') ?? $key);
            $body->addHtml($this->formGroup($form, $group));
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
        return $this->template($form)->formRest($body, $buttons);
    }

    public function formLabel(BaseControl $el, array $attrs = []): Html
    {
        if ($el instanceof Button || $el instanceof HiddenField || $el instanceof Checkbox || $el->getCaption() === null || $el->getCaption() === '') {
            return Html::el();
        }
        if (is_string($el->getOption('info'))) {
            $el->setOption('info', $this->translator->translate($el->getOption('info')));
        }
        return $this->template($el->getForm())->formLabel($el, $attrs);
    }

    public function formErrors(Component $el): Html
    {
        if ($el instanceof BaseControl) {
            return $this->template($el->getForm())->rowErrors($el->getErrors());
        }
        if ($el instanceof Form) {
            return $this->template($el->getForm())->formErrors($el->getOwnErrors());
        }
        throw new RuntimeException(get_class($el) . ' is not supported in NeoFormRenderer for rendering errors');
    }

    public function container(Container $el): Html
    {
        if ($el instanceof NeoContainer) {
            return $el->getHtml($this);
        }
        $rows = Html::el();
        foreach ($el->getComponents() as $component) {
            if ($component instanceof BaseControl || $component instanceof Container) {
                $rows->addHtml($this->formRow($component));
            }
        }
        return $rows;
    }
}
