<?php

namespace Efabrica\NeoForms\Build;

use Nette\Application\UI\Template;
use Nette\Database\Table\ActiveRow;

abstract class AbstractForm
{
    private NeoFormFactory $formFactory;

    public function __construct(NeoFormFactory $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    abstract protected function buildForm(NeoForm $form, ?ActiveRow $row): void;

    /**
     * @param ActiveRow $row
     * @return array $form->setDefaults(...)
     */
    abstract protected function initFormData(ActiveRow $row): array;

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

    protected function translate(string $message, ...$parameters): string
    {
        return $this->formFactory->getTranslator()->translate($message, ...$parameters);
    }

    protected function template(Template $template): void
    {
    }

    public function create(?ActiveRow $row = null): NeoFormControl
    {
        $form = $this->formFactory->create();
        $this->buildForm($form, $row);
        if ($row !== null) {
            $form->setDefaults($this->initFormData($row));
        }
        $form->setOnSuccess(fn(NeoForm $form, array $values) => $this->onSuccess($form, $values, $row));

        return new NeoFormControl($form, fn(Template $template) => $this->template($template));
    }
}
