<?php

namespace Efabrica\NeoForms\Control;

class SubmitButton extends \Nette\Forms\Controls\SubmitButton
{
    public function __construct($caption = null)
    {
        parent::__construct($caption);
        $this->setOption('type', 'submit');
    }

    public function setIcon(string $icon): self
    {
        $this->setOption('icon', $icon);
        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->getOption('icon');
    }
}
