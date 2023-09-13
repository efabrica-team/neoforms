<?php

namespace Efabrica\NeoForms\Build;

use Efabrica\NeoForms\Control\ControlGroupBuilder;
use Efabrica\NeoForms\Render\NeoFormRenderer;
use Nette\Forms\Container;
use Nette\Forms\Controls\BaseControl;
use Nette\HtmlStringable;
use Nette\Utils\Html;

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
     */
    public function group(?string $name = null, ?string $class = null, $label = true): ControlGroupBuilder
    {
        if ($name !== null) {
            $childGroup = ($this->childGroups[$name] ??= $this->getForm()->addGroup(null, false));
        } else {
            $childGroup = $this->getForm()->addGroup(null, false);
        }
        $childBuilder = new ControlGroupBuilder($this->getForm(), $childGroup);
        $childBuilder->setClass($class)->setLabel($label === true ? $name : $label);
        return $childBuilder;
    }

    public function getHtml(NeoFormRenderer $renderer): Html
    {
        $rows = Html::el();
        foreach ($this->getComponents() as $component) {
            if ($component instanceof BaseControl || $component instanceof Container) {
                $rows->addHtml($renderer->formRow($component));
            }
        }
        return $rows;
    }
}
