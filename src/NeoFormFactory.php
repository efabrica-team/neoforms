<?php

namespace Efabrica\NeoForms;

use Nette\Application\UI\Form;
use Nette\Localization\Translator;

abstract class NeoFormFactory
{
    private NeoFormNetteRenderer $formRenderer;
    private Translator $translator;

    public function __construct(NeoFormNetteRenderer $formRenderer, Translator $translator)
    {
        $this->formRenderer = $formRenderer;
        $this->translator = $translator;
    }

    public function create(): Form
    {
        $form = new Form();
        $form->setRenderer($this->formRenderer);
        $form->setTranslator($this->translator);
        $this->build($form);
        return $form;
    }

    abstract protected function build(Form $form): void;
}
