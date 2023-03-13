<?php

namespace Efabrica\NeoForms\Control;

use Efabrica\NeoForms\Build\DivTrait;
use Efabrica\NeoForms\Build\NeoForm;
use Nette\Forms\ControlGroup;
use Nette\HtmlStringable;
use Nette\Utils\Html;

class ControlGroupBuilder
{
    use DivTrait;

    private NeoForm $form;

    private ControlGroup $group;

    public function __construct(NeoForm $form, ControlGroup $group)
    {
        $this->form = $form;
        $this->group = $group;
    }

    /**
     * @param string|true|HtmlStringable|null $label
     * @return $this
     */
    public function setLabel($label): self
    {
        $this->group->setOption('label', $label);
        return $this;
    }

    public function setContainer(HtmlStringable $container): self
    {
        $this->group->setOption('container', $container);
        return $this;
    }

    public function setClass(?string $class): self
    {
        if ($class !== null) {
            $this->setContainer(Html::el('div')->class($class));
        }
        return $this;
    }

    /**
     * @param string|true|HtmlStringable|null $label
     */
    public function group(?string $name = null, ?string $class = null, $label = true): self
    {
        $children = $this->group->getOption('children') ?? [];
        assert(is_array($children));

        if ($name !== null) {
            $childGroup = $children[$name] ??= $this->form->addGroup(null, false);
        } else {
            $childGroup = $children[] = $this->form->addGroup(null, false);
        }

        $childBuilder = new self($this->form, $childGroup);
        $childBuilder->setClass($class)->setLabel($label === true ? $name : $label);

        $this->group->setOption('children', $children);

        return $childBuilder;
    }

    /**
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
