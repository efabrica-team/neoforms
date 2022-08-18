<?php

namespace Efabrica\NeoForms\Build;

use Closure;
use Nette\Application\UI\Control;

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

    public function render(): void
    {
        $this->template->setFile(__DIR__ . '/../Render/templates/control.latte');
        if ($this->onRender !== null) {
            $this->onRender->__invoke($this->template);
        }
        $this->template->render();
    }
}
