<?php

namespace Efabrica\NeoForms\Build;

use Nette\Database\Table\ActiveRow;

final class ExampleActiveRowForm extends ActiveRowForm
{
    private NeoFormFactory $formFactory;

    public function __construct(NeoFormFactory $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    public function create(?ActiveRow $row = null): NeoFormControl
    {
        $form = $this->formFactory->create();

        // $form->addText('title', 'Title');

        return $this->control($form, $row);
    }
}