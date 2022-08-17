<?php

namespace Efabrica\NeoForms\Build;

use Efabrica\NeoForms\Build\NeoContainerTrait;
use Efabrica\NeoForms\Build\NeoForm;
use Nette\Forms\Container;

class NeoContainer extends Container
{
    use NeoContainerTrait;

    public function getForm(bool $throw = true): NeoForm
    {
        /** @var NeoForm $form */
        return $form = $this->lookup(NeoForm::class, $throw);
    }
}
