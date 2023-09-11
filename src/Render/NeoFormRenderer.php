<?php

namespace Efabrica\NeoForms\Render;

use Efabrica\NeoForms\Build\NeoForm;
use Efabrica\NeoForms\Control\FormCollection;
use Efabrica\NeoForms\Render\Template\NeoFormTemplate;
use Latte\Engine;
use Nette\ComponentModel\Component;
use Nette\ComponentModel\IComponent;
use Nette\Forms\Container;
use Nette\Forms\ControlGroup;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\Button;
use Nette\Forms\Controls\Checkbox;
use Nette\Forms\Controls\HiddenField;
use Nette\Forms\Form;
use Nette\Localization\Translator;
use Nette\Utils\Html;
use Nette\Utils\Random;
use Nette\Utils\Strings;
use RuntimeException;

class NeoFormRenderer
{
    public const FORM_BODY_DIVIDER = 'divider';
    public NeoFormTemplate $defaultTemplate;

    private Translator $translator;
    private Engine $engine;

    public function __construct(Engine $engine, NeoFormTemplate $template, Translator $translator)
    {
        $this->defaultTemplate = $template;
        $this->translator = $translator;
        $this->engine = $engine;
    }

    protected function template(?Form $form): NeoFormTemplate
    {
        return ($form instanceof NeoForm ? $form->getTemplate() : null) ?? $this->defaultTemplate;
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

        return $this->template($el->getForm())->formRow(
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
            return $this->template($el->getForm())->readonly($el);
        }

        if (is_string($attrs['placeholder'] ?? null)) {
            $attrs['placeholder'] = $this->translator->translate($attrs['placeholder']);
        }

        $description = $el->getOption('description');
        if (is_string($description)) {
            $descriptionEl = $this->template($el->getForm())->description($description);
        } elseif ($description instanceof Html) {
            $descriptionEl = $description;
        }
        return $this->template($el->getForm())->input($el, $attrs, $descriptionEl ?? Html::el());
    }

    public function form(NeoForm $form, array $attrs = []): Html
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
        $divider = $attrs[self::FORM_BODY_DIVIDER] ?? '';
        unset($attrs[self::FORM_BODY_DIVIDER], $attrs['formErrors']);
        return $this->template($form)->form(
            $form,
            $showErrors ? $this->template($form)->formErrors($form->getOwnErrors()) : Html::el(),
            is_string($divider) ? $divider : '',
            $this->formRest($form, $attrs),
            $attrs
        );
    }

    public function formStart(NeoForm $form, array $attrs = []): Html
    {
        $attrs[self::FORM_BODY_DIVIDER] = $divider = '[___DIVIDER' . uniqid() . '___]';
        $template = $this->form($form, $attrs);
        $form->setOption('__formEnd', Strings::after($template, $divider) ?: '');
        return Html::fromHtml(Strings::before($template, $divider) ?: '');
    }

    public function formEnd(NeoForm $form): Html
    {
        $formEnd = $form->getOption('formEnd');
        if (is_string($formEnd)) {
            $form->setOption('__formEnd', null);
            return Html::fromHtml($formEnd);
        }
        return Html::el();
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
        if ($el instanceof Button || $el instanceof HiddenField || $el instanceof Checkbox) {
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
        if ($el instanceof FormCollection) {
            return $this->formCollection($el);
        }
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

    protected function formCollection(FormCollection $collection): Html
    {
        $collectionTemplate = $collection->getCollectionTemplate();
        if ($collectionTemplate !== null) {
            return Html::fromHtml($this->engine->renderToString($collectionTemplate, ['collection' => $collection]));
        }
        return $this->template($collection->getForm())->formCollection($collection, $this);
    }

    public function formCollectionItem(FormCollection $collection, IComponent $item): Html
    {
        $componentTemplate = $collection->getComponentTemplate();
        if ($componentTemplate !== null) {
            return Html::fromHtml($this->engine->renderToString($componentTemplate, ['collection' => $collection, 'item' => $item]));
        }
        if (!$item instanceof BaseControl && !$item instanceof Container) {
            throw new RuntimeException('Unsupported collection item type ' . get_class($item));
        }
        return $this->template($collection->getForm())->formCollectionItem($item, $this, $collection);
    }
}
