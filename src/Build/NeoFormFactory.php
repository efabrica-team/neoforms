<?php

namespace Efabrica\NeoForms\Build;

use Efabrica\NeoForms\Render\NeoFormNetteRenderer;
use Nette\Localization\Translator;

class NeoFormFactory
{
    private NeoFormNetteRenderer $formRenderer;
    private Translator $translator;

    public function __construct(NeoFormNetteRenderer $formRenderer, Translator $translator)
    {
        $this->formRenderer = $formRenderer;
        $this->translator = $translator;
    }

    public function create(): NeoForm
    {
        $form = new NeoForm();
        $form->setRenderer($this->formRenderer);
        $form->setTranslator($this->translator);
        return $form;
    }

    public function getTranslator(): Translator
    {
        return $this->translator;
    }
}
