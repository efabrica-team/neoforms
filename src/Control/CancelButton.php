<?php

namespace Efabrica\NeoForms\Control;

use Efabrica\NeoForms\Build\NeoForm;
use Nette\Forms\Controls\SubmitButton;

class CancelButton extends SubmitButton
{
    private ?string $flashMessage = null;

    private string $redirect = 'default';

    private array $redirectArgs = [];

    public function __construct(string $caption)
    {
        parent::__construct($caption);
        $this->setIcon();
        $this->onClick[] = function () {
            /** @var NeoForm $form */
            $form = $this->form;
            $form->finish($this->flashMessage, $this->redirect, $this->redirectArgs);
        };
    }

    public function setIcon(string $icon = 'cancel'): self
    {
        return $this->setOption('icon', $icon);
    }

    public function setFinish(?string $flashMessage = null, string $redirect = 'default', array $redirectArgs = []): self
    {
        $this->flashMessage = $flashMessage;
        $this->redirect = $redirect;
        $this->redirectArgs = $redirectArgs;
        return $this;
    }
}
