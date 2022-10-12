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
        if (is_string($name)) {
            $this->group = $this->form->getGroup($name) ?? $this->form->addGroup($name, false);
        } else {
            $this->group = $this->form->addGroup((++self::$groupCounter) . '#' . $class, false);
        }
        $this->group->setOption('container', Html::el('div')->setAttribute('class', $class));
    }

    public function group(?string $name = null, ?string $class = null): self
    {
        $child = new self($this->form, $class ?? 'c-form', $name);
        $children = $this->group->getOption('children');
        $this->group->setOption('children', [...(is_iterable($children) ? $children : []), $child->group]);
        return $child;
    }

    public function row(?string $name = null): self
    {
        return $this->group($name, 'row');
    }

    public function col(?string $col = null, ?string $name = null): self
    {
        return $this->group($name, 'col' . (trim((string)$col) === '' ? '' : '-') . $col);
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
