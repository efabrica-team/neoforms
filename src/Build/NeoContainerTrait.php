<?php

namespace Efabrica\NeoForms\Build;

use Efabrica\NeoForms\Control\ControlGroupBuilder;
use Efabrica\NeoForms\Control\StaticTags;
use Efabrica\NeoForms\Control\Tags;
use Efabrica\NeoForms\Control\ToggleSwitch;
use Efabrica\Nette\Chooze\ChoozeControl;
use Efabrica\Nette\Forms\Rte\RteControl;
use Nette\Application\UI\Multiplier;
use Nette\Forms\Container;
use RadekDostal\NetteComponents\DateTimePicker\TbDatePicker;
use RadekDostal\NetteComponents\DateTimePicker\TbDateTimePicker;

/**
 * @uses \Efabrica\NeoForms\Build\NeoForm
 * @uses \Efabrica\NeoForms\Build\NeoContainer
 */
trait NeoContainerTrait
{
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

    public function addRte(string $name, ?string $label = null): RteControl
    {
        $component = new RteControl($label);
        $this->addComponent($component, $name);
        return $component;
    }

    /**
     * @return NeoForm
     */
    public function group(?string $name = null, ?string $class = null)
    {
        /** @var NeoForm $group */
        $group = new ControlGroupBuilder($this, $class ?? 'c-form', $name);
        return $group;
    }

    /**
     * @return NeoForm to fool the IDE into adding all ->add*() methods
     */
    public function row(?string $name = null)
    {
        /** @var NeoForm $group */
        $group = new ControlGroupBuilder($this, 'row', $name);
        return $group;
    }

    /**
     * @return NeoForm to fool the IDE into adding all ->add*() methods
     */
    public function col(?string $col = '', ?string $name = null)
    {
        /** @var NeoForm $group */
        $group = new ControlGroupBuilder($this, 'col' . ($col ? '-' : '') . $col, $name);
        return $group;
    }

    /**
     * @see Registrator
     */
    public function addChooze(
        string $name,
        string $type,
        ?string $label = null,
        bool $multiple = false,
        array $urlParams = [],
        array $uploadParams = []
    ): ChoozeControl {
        $this->__call('addChooze' . ucfirst($type), func_get_args());
        $control = $this->getComponent($name);
        assert($control instanceof ChoozeControl);
        return $control;
    }

    public function addMultiplier(string $name, callable $factory): Multiplier
    {
        $component = new Multiplier($factory);
        $this->addComponent($component, $name);
        return $component;
    }

    /**
     * @param $name
     * @return NeoContainer
     */
    public function addContainer($name): Container
    {
        $control = new NeoContainer();
        $control->currentGroup = $this->currentGroup;
        if ($this->currentGroup !== null) {
            $this->currentGroup->add($control);
        }

        $this->addComponent($control, $name);
        return $control;
    }
}
