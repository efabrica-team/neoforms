<?php

namespace Efabrica\NeoForms\Control;

use Nette\Forms\Controls\SubmitButton as NetteSubmitButton;

class SubmitButton extends NetteSubmitButton
{
    public function setIcon(?string $icon): self
    {
        $this->setOption('icon', $icon);
        return $this;
    }

    public function getIcon(): ?string
    {
        $icon = $this->getOption('icon');
        return is_string($icon) ? $icon : null;
    }
}
