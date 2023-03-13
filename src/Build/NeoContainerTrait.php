<?php

namespace Efabrica\NeoForms\Build;

use Efabrica\NeoForms\Control\CodeEditor;
use Efabrica\NeoForms\Control\StaticTags;
use Efabrica\NeoForms\Control\Tags;
use Efabrica\NeoForms\Control\ToggleSwitch;
use JetBrains\PhpStorm\ExpectedValues;
use Nette\Application\UI\Multiplier;
use RadekDostal\NetteComponents\DateTimePicker\TbDatePicker;
use RadekDostal\NetteComponents\DateTimePicker\TbDateTimePicker;

/**
 * @used-by \Efabrica\NeoForms\Build\NeoForm
 * @used-by \Efabrica\NeoForms\Build\NeoContainer
 */
trait NeoContainerTrait
{
    use DivTrait;

    protected array $options = [];

    /**
     * @return mixed|null
     */
    public function getOption(string $name)
    {
        return $this->options[$name] ?? null;
    }

    /**
     * @param mixed $value
     * @return $this
     */
    public function setOption(string $name, $value): self
    {
        $this->options[$name] = $value;
        return $this;
    }

    public function addToggleSwitch(string $name, ?string $label = null): ToggleSwitch
    {
        $component = new ToggleSwitch($label);
        $this->addComponent($component, $name);
        return $component;
    }

    public function addTags(string $name, ?string $label = null, array $config = [], ?string $placeholder = null): Tags
    {
        $component = new Tags($label, $config, $placeholder);
        $this->addComponent($component, $name);
        return $component;
    }

    public function addStaticTags(
        string $name,
        ?string $label,
        array $choices,
        bool $allowCustomTags = false,
        ?string $placeholder = null
    ): StaticTags {
        $component = new StaticTags($label, $choices, $allowCustomTags, $placeholder);
        $this->addComponent($component, $name);
        return $component;
    }

    public function addDatePicker(string $name, string $label = null, int $maxLength = null): TbDatePicker
    {
        $component = new TbDatePicker($label, $maxLength);
        $this->addComponent($component, $name);
        return $component;
    }

    public function addDateTimePicker(string $name, string $label = null, int $maxLength = null): TbDateTimePicker
    {
        $component = new TbDateTimePicker($label, $maxLength);
        $this->addComponent($component, $name);
        return $component;
    }

    public function addMultiplier(string $name, callable $factory): Multiplier
    {
        $component = new Multiplier($factory);
        $this->addComponent($component, $name);
        return $component;
    }

    /**
     * @param value-of<CodeEditor::MODES> $mode
     */
    public function addCodeEditor(
        string $name,
        #[ExpectedValues(CodeEditor::MODES)]
        string $mode,
        ?string $label = null
    ): CodeEditor {
        $component = new CodeEditor($mode, $label);
        $this->addComponent($component, $name);
        return $component;
    }

    public function addContainer($name): NeoContainer
    {
        $control = new NeoContainer();
        $control->currentGroup = $this->currentGroup;
        if ($this->currentGroup !== null) {
            $this->currentGroup->add($control);
        }

        $this->addComponent($control, is_int($name) ? "$name" : $name);
        return $control;
    }
}
