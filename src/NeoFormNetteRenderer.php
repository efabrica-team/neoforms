<?php

namespace Efabrica\NeoForms;

use Nette\Forms\Form;
use Nette\Forms\FormRenderer;

class NeoFormNetteRenderer implements FormRenderer
{
    private NeoFormRenderer $renderer;

    public function setRenderer(NeoFormRenderer $renderer): void
    {
        $this->renderer = $renderer;
    }

    public function render(Form $form): string
    {
        return $this->renderer->formStart($form).$this->renderer->formEnd($form);
    }
}
