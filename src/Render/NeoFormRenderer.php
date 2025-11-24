<?php

namespace Efabrica\NeoForms\Render;

use Efabrica\NeoForms\Build\NeoContainer;
use Efabrica\NeoForms\Build\NeoForm;
use Efabrica\NeoForms\Render\Template\NeoFormTemplate;
use Generator;
use Nette\ComponentModel\Component;
use Nette\ComponentModel\IComponent;
use Nette\Forms\Container;
use Nette\Forms\Control;
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

    public bool $isReadonly = false;

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

        $this->isReadonly = $attrs['readonly'] ?? $form->isReadonly() || $form->isReadonlyAttr();
        /** @var BaseControl $control */
        foreach ($form->getComponents(true, BaseControl::class) as $control) {
            $control->setOption('rendered', false);
            if ($this->isReadonly) {
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
        foreach ($this->getGroupControls($group) as $control) {
            if ($control instanceof BaseControl || $control instanceof Container) {
                if (!$this->isRendered($control)) {
                    $body->addHtml($this->formRow($control));
                }
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

        if ($this->isRendered($el)) {
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
        if (($attrs['readonly'] ?? (bool)$el->getOption('readonly')) || $el->getForm()->isReadonlyAttr()) {
            $el->setAttribute('readonly', true);
        }

        if ($el->getForm()->isReadonly()) {
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
        $groups = [];
        foreach ($form->getGroups() as $key => $group) {
            $group->setOption('label', $group->getOption('label') ?? $key);
            $groups[$key] = $this->formGroup($form, $group);
        }

        $buttons = [];
        $body = Html::el();
        foreach ($form->getComponents() as $component) {
            if ($this->isRendered($component)) {
                continue;
            }
            if (!$component instanceof Button) {
                if ($component instanceof BaseControl || $component instanceof Container) {
                    $body->addHtml($this->formRow($component));
                }
            } elseif ($options['buttons'] ?? true) {
                $buttons[] = $this->formRow($component);
            }
        }
        return $this->template($form)->formRest($body, $groups, $buttons);
    }

    public function formLabel(BaseControl $el, array $attrs = []): Html
    {
        if ($el instanceof Button || $el instanceof HiddenField || $el instanceof Checkbox) {
            return Html::el();
        }

        /** @var string|null $caption */
        $caption = $el->getCaption();
        if ($caption === null || $caption === '') {
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
        if ($this->isRendered($el)) {
            return Html::el();
        }
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

    /**
     * @param ControlGroup $group
     * @return iterable<Control|Container>
     */
    public function getGroupControls(ControlGroup $group): iterable
    {
        $children = [];
        foreach ($group->getControls() as $control) {
            if (!$control instanceof BaseControl) {
                $children[] = $control;
                continue;
            }
            $container = $this->findContainer($control);
            if ($container === null) {
                $children[] = $control;
            } elseif (!in_array($container, $children, true)) {
                $children[] = $container;
            }
        }
        return $children;
    }

    public function findContainer(BaseControl $control): ?NeoContainer
    {
        $parent = $control;
        $container = null;
        while ($parent !== null) {
            $parent = $parent->getParent();
            if ($parent instanceof NeoContainer && $parent->isSingleRender()) {
                $container = $parent;
            }
        }
        return $container;
    }

    private function isRendered(IComponent $control): bool
    {
        if ($control instanceof BaseControl) {
            return $control->getOption('rendered') === true;
        }
        if ($control instanceof NeoContainer && $control->isSingleRender()) {
            $components = $control->getComponents();
            if (is_array($components)) {
                $first = reset($components);
            } else {
                $first = $control->getComponents()->current();
            }
            return $this->isRendered($first);
        }
        return false;
    }
}
