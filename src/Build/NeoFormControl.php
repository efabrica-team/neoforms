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
        $this->onRender = is_callable($onRender) ? Closure::fromCallable($onRender) : null;
    }

    public function render(): void
    {
        assert($this->template instanceof Template);
        if ($this->template->getFile() === null) {
            $this->form->render();
            return;
        }
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
