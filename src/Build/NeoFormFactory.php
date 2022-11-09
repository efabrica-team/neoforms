<?php

namespace Efabrica\NeoForms\Build;

use Efabrica\NeoForms\Render\NeoFormNetteRenderer;
use Efabrica\Nette\Forms\Rte\Registrator;
use Nette\DI\Container;
use Nette\Localization\Translator;

class NeoFormFactory
{
    private NeoFormNetteRenderer $formRenderer;

    private Translator $translator;

    /** @var Registrator */
    private $rteRegistrator;

    public function __construct(NeoFormNetteRenderer $formRenderer, Translator $translator, Container $container)
    {
        $this->formRenderer = $formRenderer;
        $this->translator = $translator;
        $this->rteRegistrator = $container->getByType(Registrator::class, false);
    }

    public function create(): NeoForm
    {
        $form = new NeoForm();
        $form->setRenderer($this->formRenderer);
        $form->setTranslator($this->translator);
        $form->setOption('rteRegistrator', $this->rteRegistrator);
        return $form;
    }

    public function getTranslator(): Translator
    {
        return $this->translator;
    }
}
