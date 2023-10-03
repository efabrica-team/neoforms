<?php

namespace Efabrica\NeoForms\Build;

use Closure;
use Nette\Application\UI\Control;
use Nette\Application\UI\Template;

/**
 * @property-read Template $template
 */
class NeoFormControl extends Control
{
    /** @readonly */
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
        if ($this->onRender !== null) {
            ($this->onRender)($this->template);
        }
        if ($this->template->getFile() === null) {
            $this->form->render();
            return;
        }
        $this->template->form = $this->form;
        $this->template->render();
    }

    public function getForm(): NeoForm
    {
        return $this->form;
    }
}
