<?php

namespace Efabrica\NeoForms\Control;

use Closure;
use Efabrica\NeoForms\Build\DivTrait;
use Efabrica\NeoForms\Build\NeoContainer;
use Efabrica\NeoForms\Build\NeoForm;
use Nette\Application\UI\Multiplier;
use Nette\Forms\ControlGroup;
use Nette\Forms\Controls\Button;
use Nette\Forms\Controls\Checkbox;
use Nette\Forms\Controls\CheckboxList;
use Nette\Forms\Controls\RadioList;
use Nette\Forms\Controls\TextArea;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Controls\UploadControl;
use Nette\HtmlStringable;
use Nette\Utils\Html;
use RadekDostal\NetteComponents\DateTimePicker\TbDatePicker;
use RadekDostal\NetteComponents\DateTimePicker\TbDateTimePicker;
use Stringable;

/**
 * @mixin NeoForm
 * @method TextInput addText(string $name, string|Stringable|null $label = null, ?int $cols = null, ?int $maxLength = null)
 * @method TextInput addPassword(string $name, string|Stringable|null $label = null, ?int $cols = null, ?int $maxLength = null)
 * @method TextInput addInteger(string $name, string|Stringable|null $label = null)
 * @method TextArea addTextArea(string $name, string|Stringable|null $label = null, ?int $cols = null, ?int $rows = null)
 * @method UploadControl addUpload(string $name, string|Stringable|null $label = null)
 * @method Checkbox addCheckbox(string $name, string|Stringable|null $caption = null)
 * @method RadioList addRadioList(string $name, string|Stringable|null $label = null, ?array $items = null)
 * @method CheckboxList addCheckboxList(string $name, string|Stringable|null $label = null, ?array $items = null)
 * @method SelectBox addSelect(string $name, Stringable|string|null $label = null, ?array $items = null, ?int $size = null)
 * @method MultiSelectBox addMultiSelect(string $name, Stringable|string|null $label = null, ?array $items = null, ?int $size = null)
 * @method ToggleSwitch addToggleSwitch(string $name, Stringable|string|null $label = null)
 * @method Tags addTags(string $name, Stringable|string|null $label = null, array $config = [], ?string $placeholder = null)
 * @method StaticTags addStaticTags(string $name, Stringable|string|null $label, array $choices, bool $allowCustomTags = false, ?string $placeholder = null)
 * @method TbDatePicker addDatePicker(string $name, Stringable|string|null $label = null, ?int $maxLength = null)
 * @method TbDateTimePicker addDateTimePicker(string $name, Stringable|string|null $label = null, ?int $maxLength = null)
 * @method Multiplier addMultiplier(string $name, callable $factory)
 * @method CodeEditor addCodeEditor(string $name, string $mode, ?string $label = null)
 * @method SubmitButton addSubmit(string $name, string|Stringable|null $caption = null, ?Closure $onSubmit = null)
 * @method NeoContainer addContainer(int|string $name, ?NeoContainer $container = null)
 * @method FormCollection addCollection(string $name, string $label, callable $factory)
 * @method Button addButton(string $name, string|Stringable|null $caption = null, ?string $icon = null)
 */
class ControlGroupBuilder
{
    use DivTrait;

    private NeoForm $form;

    private ControlGroup $group;

    public function __construct(NeoForm $form, ControlGroup $group, private readonly ?self $parent = null)
    {
        $this->form = $form;
        $this->group = $group;
    }

    /**
     * Returns the builder this group was created from via group()/row()/col().
     * Top-level builders (created directly from NeoForm/NeoContainer) return themselves.
     */
    public function end(): self
    {
        return $this->parent ?? $this;
    }

    /**
     * @param string|bool|HtmlStringable|null $label
     */
    public function setLabel($label): self
    {
        return $this->setOption('label', $label);
    }

    public function setContainer(HtmlStringable $container): self
    {
        return $this->setOption('container', $container);
    }

    public function setClass(?string $class): self
    {
        if ($class !== null) {
            $this->setContainer(Html::el('div')->class($class));
        }
        return $this;
    }

    /**
     * @param mixed $value
     */
    public function setOption(string $key, $value): self
    {
        $this->group->setOption($key, $value);
        return $this;
    }

    /**
     * @param string|true|HtmlStringable|null $label
     */
    public function group(?string $name = null, ?string $class = null, string|true|HtmlStringable|null $label = true): self
    {
        $children = $this->group->getOption('children') ?? [];
        assert(is_array($children));

        if ($name !== null) {
            $childGroup = $children[$name] ??= $this->form->addGroup(null, false);
        } else {
            $childGroup = $children[] = $this->form->addGroup(null, false);
        }

        $childBuilder = new self($this->form, $childGroup, $this);
        $childBuilder->setClass($class)->setLabel($label === true ? $name : $label);

        $this->group->setOption('children', $children);

        return $childBuilder;
    }

    /**
     * @param array<int, mixed> $arguments
     * @return mixed
     */
    public function __call(string $name, array $arguments = [])
    {
        $prevGroup = $this->form->getCurrentGroup();
        $this->form->setCurrentGroup($this->group);
        $return = $this->form->$name(...$arguments);
        $this->form->setCurrentGroup($prevGroup);
        return $return;
    }
}
