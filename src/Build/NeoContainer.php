<?php

namespace Efabrica\NeoForms\Build;

use Efabrica\NeoForms\Control\ControlGroupBuilder;
use Efabrica\NeoForms\Render\NeoFormRenderer;
use Nette\Forms\Container;
use Nette\Forms\Controls\BaseControl;
use Nette\HtmlStringable;
use Nette\Localization\Translator;
use Nette\Utils\Html;

/**
 * @method NeoForm getForm(bool $throw = true)
 */
class NeoContainer extends Container
{
    use NeoContainerTrait;

    private array $childGroups = [];

    private bool $singleRender = false;

    /**
     * @param string|true|HtmlStringable|null $label
     *      true = same as name
     *      null = no label
     *      HtmlStringable = custom Html (Html::el())
     *      string = custom label with default Html
     */
    public function group(?string $name = null, ?string $class = null, string|true|HtmlStringable|null $label = true): ControlGroupBuilder
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

    public function isSingleRender(): bool
    {
        return $this->singleRender;
    }

    public function setSingleRender(bool $singleRender): self
    {
        $this->singleRender = $singleRender;
        return $this;
    }

    public function getTranslator(): ?Translator
    {
        return $this->getForm()->getTranslator();
    }
}
