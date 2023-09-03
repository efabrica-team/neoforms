<?php

namespace Efabrica\NeoForms\Render;

use Efabrica\NeoForms\Build\NeoForm;
use Latte\Engine;
use Nette\Forms\Form;
use Nette\Forms\FormRenderer;
use RuntimeException;

class NeoFormNetteRenderer implements FormRenderer
{
    private NeoFormRenderer $renderer;

    public function __construct(NeoFormRenderer $renderer)
    {
        $this->renderer = $renderer;
    }

    public function render(Form $form): string
    {
        if (!$form instanceof NeoForm) {
            throw new RuntimeException('form is not instance of NeoForm');
        }
        return $this->renderer->formStart($form) . $this->renderer->formEnd($form);
    }
}
