<?php

namespace Efabrica\NeoForms\Build;

use Nette\Forms\Container;

class NeoContainer extends Container
{
    use NeoContainerTrait;

    public function getForm(bool $throw = true): NeoForm
    {
        $form = $this->lookup(NeoForm::class, $throw);
        assert($form instanceof NeoForm);
        return $form;
    }
}
