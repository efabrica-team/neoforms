<?php

namespace Efabrica\NeoForms\Build;

use Closure;
use Nette\Application\UI\Control;
use Nette\Application\UI\Template;

class NeoFormControl extends Control
{
    public NeoForm $form;

    /** @var Closure|null fn(Template $template): void */
    private ?Closure $onRender = null;

    public function __construct(NeoForm $form, ?callable $onRender = null)
    {
        $this->addComponent($form, 'form');
        $this->form = $form;
        $this->onRender = $onRender ? Closure::fromCallable($onRender) : null;
    }

    public static function withTemplate(NeoForm $form, string $templatePath): self
    {
        return new NeoFormControl($form, fn(Template $template) => $template->setFile($templatePath));
    }

    public function render(): void
    {
        $this->template->setFile(__DIR__ . '/../Render/templates/control.latte');
        if ($this->onRender !== null) {
            ($this->onRender)($this->template);
        }
        $this->template->render();
    }

    public function getForm(): NeoForm
    {
        return $this->form;
    }
}
