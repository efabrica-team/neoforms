<?php

namespace Efabrica\NeoForms\Control;

use Efabrica\NeoForms\Build\NeoForm;
use Nette\Forms\ControlGroup;
use Nette\Utils\Html;

class ControlGroupBuilder
{
    private NeoForm $form;
    private ControlGroup $group;
    private static int $groupCounter = 0;

    public function __construct(NeoForm $form, string $class, ?string $name)
    {
        $this->form = $form;
        $this->group = $this->form->getGroup($name) ?? $this->form->addGroup($name ?? (++self::$groupCounter).'#'.$class, false);
        $this->group->setOption('container', Html::el('div')->setAttribute('class', $class));
    }

    public function group(?string $name = null, ?string $class = null): self
    {
        $child = new self($this->form, $class ?? '', $name);
        $this->group->setOption('children', [...($this->group->getOption('children') ?? []), $child->group]);
        return $child;
    }

    public function row(?string $name = null): self
    {
        $child = new self($this->form, 'row', $name);
        $this->group->setOption('children', [...($this->group->getOption('children') ?? []), $child->group]);
        return $child;
    }

    public function col(string $column, ?string $name = null): self
    {
        $child = new self($this->form, 'col-'.$column, $name);
        $this->group->setOption('children', [...($this->group->getOption('children') ?? []), $child->group]);
        return $child;
    }

    public function __call($name, $arguments)
    {
        $prevGroup = $this->form->getCurrentGroup();
        $this->form->setCurrentGroup($this->group);
        $return = $this->form->$name(...$arguments);
        $this->form->setCurrentGroup($prevGroup);
        return $return;
    }
}
