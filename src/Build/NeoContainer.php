<?php

namespace Efabrica\NeoForms\Build;

use Efabrica\NeoForms\Control\ControlGroupBuilder;
use Nette\Forms\Container;
use Nette\Forms\Controls\MultiSelectBox;
use Nette\Forms\Controls\SelectBox;
use Nette\HtmlStringable;

class NeoContainer extends Container
{
    use NeoContainerTrait;

    private array $childGroups = [];

    public function getForm(bool $throw = true): NeoForm
    {
        $form = $this->lookup(NeoForm::class, $throw);
        assert($form instanceof NeoForm);
        return $form;
    }

    /**
     * @param string|true|HtmlStringable|null $label
     *      true = same as name
     *      null = no label
     *      HtmlStringable = custom Html (Html::el())
     *      string = custom label with default Html
     * @return NeoForm to fool the static analysis into seeing all ->add*() methods
     */
    public function group(?string $name = null, ?string $class = null, $label = true)
    {
        if ($name !== null) {
            $childGroup = ($this->childGroups[$name] ??= $this->getForm()->addGroup(null, false));
        } else {
            $childGroup = $this->getForm()->addGroup(null, false);
        }
        $childBuilder = new ControlGroupBuilder($this->getForm(), $childGroup);
        $childBuilder->setClass($class)->setLabel($label === true ? $name : $label);
        /** @var NeoForm $childBuilder */
        return $childBuilder;
    }
}
