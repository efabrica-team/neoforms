<?php

namespace Efabrica\NeoForms\Build;

use Nette\Application\UI\Control;

class NeoFormControl extends Control
{
    public NeoForm $form;
    public string $templatePath;
    public array $templateVars;

    public function __construct(NeoForm $form, ?string $template = null, array $templateVars = [])
    {
        $this->addComponent($form, 'form');
        $this->templatePath = $template ?? __DIR__ . '/../Render/templates/control.latte';
        $this->templateVars = $templateVars;
        $this->form = $form;
    }

    public function render(): void
    {
        $this->template->setFile($this->templatePath);
        $this->template->setParameters($this->templateVars);
        $this->template->render();
    }
}
