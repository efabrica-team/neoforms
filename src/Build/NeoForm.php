<?php

namespace Efabrica\NeoForms\Build;

use Efabrica\NeoForms\Control\Col;
use Efabrica\NeoForms\Control\Div;
use Efabrica\NeoForms\Control\GroupBuilder;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;

class NeoForm extends Form
{
    private bool $readonly = false;

    use NeoContainerTrait;

    public function setReadonly(bool $readonly = true): void
    {
        $this->readonly = $readonly;
    }

    public function isReadonly(): bool
    {
        return $this->readonly;
    }

    /**
     * @param scalar[]|scalar $redirectArgs
     */
    public function finish(string $flashMessage, string $redirect = 'default', $redirectArgs = []): void
    {
        $presenter = $this->getPresenter();
        assert($presenter instanceof Control);
        $presenter->flashMessage($this->getTranslator()->translate($flashMessage), 'success');
        $presenter->redirect($redirect, $redirectArgs);
    }
}
