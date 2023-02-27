<?php

namespace Efabrica\NeoForms\Build;

use Nette\Application\UI\Template;

abstract class FormDefinition
{
    // abstract public function create([never ...$args]): NeoFormControl

    protected function control(NeoForm $form): NeoFormControl
    {
        $form->setOnSuccess(fn(NeoForm $form, array $values) => $this->onSuccess($form, $values));

        return new NeoFormControl($form, fn(Template $template) => $this->template($template));
    }

    protected function onSuccess(NeoForm $form, array $values): void
    {
    }

    protected function template(Template $template): void
    {
    }
}
