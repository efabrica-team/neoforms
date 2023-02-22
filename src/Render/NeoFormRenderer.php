<?php

namespace Efabrica\NeoForms\Render;

use Efabrica\NeoForms\Build\NeoForm;
use Latte\Engine;
use Nette\Forms\Container;
use Nette\Forms\ControlGroup;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\Button;
use Nette\Forms\Controls\Checkbox;
use Nette\Forms\Controls\HiddenField;
use Nette\Forms\Form;
use Nette\Localization\Translator;
use Nette\Utils\Html;
use Nette\Utils\Strings;
use RuntimeException;

class NeoFormRenderer
{
    private Engine $engine;

    private string $template;

    public NeoInputRenderer $inputRenderer;

    public function __construct(Engine $engine, Translator $translator)
    {
        $this->engine = $engine;
        $this->template = __DIR__ . '/templates/chroma.latte';
        $this->inputRenderer = new NeoInputRenderer($this, $translator);
    }

    public function block(string $blockName, array $attrs = []): string
    {
        return $this->engine->renderToString($this->template, $attrs, $blockName);
    }

    /**
     * @param scalar|true|null|mixed $name
     */
    public function group(ControlGroup $group, $name = null): string
    {
        $container = $group->getOption('container') ?? Html::el();
        assert($container instanceof Html);
        $label = $group->getOption('label', Html::el());
        if (is_string($name) && $label === true) {
            $label = $name;
        }

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
                $body->addHtml($this->container($control, ['rest' => true]));
            }
        }

        if (Strings::trim($body->getText()) === '') {
            return '';
        }

        return $container->addHtml(
            $this->block('group', ['body' => $body, 'label' => $label])
        )->toHtml();
    }

    /**
     * @param BaseControl|Container $el
     * @param array                 $options ['rest' => `if true, doesn't render controls that were already rendered`]
     * @return string
     */
    public function row($el, array $options = []): string
    {
        if ($el instanceof Container) {
            return $this->container($el, $options);
        }

        if ($options['rest'] ?? false) {
            /** @var bool $rendered */
            $rendered = $el->getOption('rendered') ?? false;
            if ($rendered) {
                return '';
            }
        }

        if ($options['readonly'] ?? false) {
            $options['input']['readonly'] = $options['readonly'];
        }
        if ($el instanceof HiddenField) {
            return $this->block('hiddenRow', [
                'inside' => '',
                'input' => Html::fromHtml($this->inputRenderer->input($el, $options['input'] ?? [])),
                'attrs' => array_filter($options, 'is_scalar'),
                'options' => $options,
            ]);
        }

        if ($el instanceof Checkbox) {
            $label = '';
            $options['input'] ??= [];
            $options['input']['caption'] = true;
        } else {
            $label = $this->label($el, $options['label'] ?? []);
        }

        return $this->block('row', [
            'inside' => '',
            'label' => Html::fromHtml($label),
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

        $inside = uniqid();
        return Strings::before($this->block('row', [
            'inside' => $inside,
            'label' => '',
            'input' => '',
            'errors' => '',
            'attrs' => array_filter($options, 'is_scalar'),
            'options' => $options,
        ]), $inside) ?? '';
    }

    public function rowGroupEnd(BaseControl $el, array $options = []): string
    {
        if ($el instanceof HiddenField) {
            return '';
        }

        $inside = uniqid();
        return Strings::after($this->block('row', [
            'inside' => $inside,
            'label' => '',
            'input' => '',
            'errors' => '',
            'attrs' => array_filter($options, 'is_scalar'),
            'options' => $options,
        ]), $inside) ?? '';
    }

    public function formStart(Form $form, array $options = []): string
    {
        $form->fireRenderEvents();
        /** @var BaseControl $control */
        foreach ($form->getControls() as $control) {
            $control->setOption('rendered', false);
        }
        if ($options['readonly'] ?? ($form instanceof NeoForm && $form->isReadonly())) {
            foreach ($form->getControls() as $control) {
                assert($control instanceof BaseControl);
                $control->setOption('readonly', $control->getOption('readonly') ?? true);
            }
        }
        $inside = uniqid();
        return Strings::before($this->block('form', [
            'form' => $form,
            'attrs' => $form->getElementPrototype()->attrs + array_filter($options, 'is_scalar'),
            'inside' => $inside,
            'errors' => $form->getOwnErrors(),
            'options' => $options,
            'renderRest' => false,
            'formErrors' => $options['formErrors'] ?? true,
        ]), $inside) ?? '';
    }

    public function formEnd(Form $form, array $options = []): string
    {
        $inside = uniqid();
        return Strings::after($this->block('form', [
            'form' => $form,
            'attrs' => $form->getElementPrototype()->attrs + array_filter($options, 'is_scalar'),
            'inside' => $inside,
            'errors' => $form->getOwnErrors(),
            'options' => $options,
            'renderRest' => $options['rest'] ?? true,
            'formErrors' => $options['formErrors'] ?? true,
        ]), $inside) ?? '';
    }

    public function formRest(Form $form, array $options = []): string
    {
        $groupHtml = Html::el();
        foreach ($form->getGroups() as $key => $group) {
            $groupHtml->addHtml($this->group($group, $key));
        }

        $rest = $buttons = [];
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
                $rest[] = $component;
            } elseif ($options['buttons'] ?? true) {
                $buttons[] = $component;
            }
        }
        return $this->block('formRest', [
            'renderer' => $this,
            'groups' => $groupHtml,
            'form' => $form,
            'rest' => $rest,
            'buttons' => $buttons,
        ]);
    }

    public function label(BaseControl $el, array $options = []): string
    {
        if ($el instanceof Button || $el instanceof HiddenField) {
            return '';
        }
        return $this->block('label', [
            'for' => $el->getHtmlId(),
            'caption' => $el->getCaption(),
            'info' => $el->getOption('info'),
            'errors' => $el->getErrors(),
            'required' => $el->isRequired(),
            'attrs' => array_filter($options, 'is_scalar'),
        ]);
    }

    /**
     * @param BaseControl|Form|object $el
     * @param array                   $options
     * @return string
     */
    public function errors($el, array $options): string
    {
        if ($el instanceof BaseControl) {
            return $this->block('errors', [
                'errors' => $el->getErrors(),
                'options' => $options,
            ]);
        }
        if ($el instanceof Form) {
            return $this->block('formErrors', [
                'errors' => $el->getOwnErrors(),
                'options' => $options,
            ]);
        }
        throw new RuntimeException(get_class($el) . ' is not yet supported in NeoFormRenderer');
    }

    public function sectionStart(string $caption): string
    {
        $sep = uniqid();
        return Strings::before($this->block('section', [
            'inside' => $sep,
            'caption' => $caption,
        ]), $sep) ?? '';
    }

    public function sectionEnd(string $caption): string
    {
        $sep = uniqid();
        return Strings::after($this->block('section', [
            'inside' => $sep,
            'caption' => $caption,
        ]), $sep) ?? '';
    }

    public function getTemplatePath(): string
    {
        return $this->template;
    }

    public function setTemplatePath(string $templatePath): void
    {
        $this->template = $templatePath;
    }

    public function container(Container $el, array $options): string
    {
        $rows = [];
        foreach ($el->getComponents() as $component) {
            if ($component instanceof BaseControl || $component instanceof Container) {
                $rows[] = $this->row($component, ['rest' => ($options['rest'] ?? false)]);
            }
        }
        return implode("\n", $rows);
    }
}
