<?php

namespace Efabrica\NeoForms\Render;

use Latte\Engine;
use Nette\Forms\Form;
use Nette\Forms\FormRenderer;
use RuntimeException;

class NeoFormNetteRenderer implements FormRenderer
{
    private ?NeoFormRenderer $renderer = null;

    public function init(Engine $engine): void
    {
        $provider = $engine->getProviders()['neoFormRenderer'];
        assert($provider instanceof NeoFormRenderer);
        $this->renderer = $provider;
    }

    public function render(Form $form): string
    {
        if ($this->renderer instanceof NeoFormRenderer) {
            return $this->renderer->formStart($form) . $this->renderer->formEnd($form);
        }
        throw new RuntimeException('neonFormRenderer not set');
    }
}
