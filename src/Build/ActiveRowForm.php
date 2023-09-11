<?php

namespace Efabrica\NeoForms\Build;

use Nette\Application\UI\Template;
use Nette\Database\Table\ActiveRow;

abstract class ActiveRowForm extends FormDefinition
{
    // abstract public function create([never ...$args]): NeoFormControl

    final protected function control(NeoForm $form, ?ActiveRow $row = null): NeoFormControl
    {
        if ($row !== null) {
            $form->setDefaults($this->initFormData($row));
        }

        $form->setOnSuccess(fn(NeoForm $form, array $values) => $this->onSuccess($form, $values, $row));

        return new NeoFormControl($form, fn(Template $template) => $this->template($template));
    }

    /**
     * @param ActiveRow $row
     * @return array $form->setDefaults(...)
     */
    protected function initFormData(ActiveRow $row): array
    {
        return $row->toArray();
    }

    protected function onSuccess(NeoForm $form, array $values, ?ActiveRow $row = null): void
    {
        if ($row === null) {
            $this->onCreate($form, $values);
        } else {
            $this->onUpdate($form, $values, $row);
        }
    }

    protected function onCreate(NeoForm $form, array $values): void
    {
    }

    protected function onUpdate(NeoForm $form, array $values, ActiveRow $row): void
    {
    }

    protected function template(Template $template): void
    {
    }
}
