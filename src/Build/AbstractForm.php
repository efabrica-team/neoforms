<?php

namespace Efabrica\NeoForms\Build;

use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Database\Table\ActiveRow;
use Throwable;
use Tracy\Debugger;
use Tracy\ILogger;

abstract class AbstractForm
{
    private NeoFormFactory $formFactory;

    public function __construct(NeoFormFactory $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    abstract protected function buildForm(NeoForm $form, ?ActiveRow $row): void;

    abstract protected function initFormData(ActiveRow $row): array;

    protected function onSuccess(NeoForm $form, array $values): void
    {
        // this is called on form success
    }

    protected function onCreate(NeoForm $form, array $values): void
    {
        // this is called after onSuccess, if $row is null
    }

    protected function onUpdate(NeoForm $form, array $values, ActiveRow $row): void
    {
        // this is called after onSuccess, if $row is not null
    }

    protected function translate(string $message, ...$parameters): string
    {
        return $this->formFactory->getTranslator()->translate($message, ...$parameters);
    }

    protected function templateFile(): ?string
    {
        // use this to change the form template
        return null;
    }

    protected function templateVars(NeoForm $form, ?ActiveRow $row): array
    {
        // use this to pass variables to the form template
        return [];
    }

    public function create(?ActiveRow $row = null): NeoFormControl
    {
        $form = $this->formFactory->create();
        $this->buildForm($form, $row);
        if ($row !== null) {
            $form->setDefaults($this->initFormData($row));
        }
        $form->onSuccess[] = function (NeoForm $form, array $values) use ($row) {
            try {
                $this->onSuccess($form, $values);
                if ($row === null) {
                    $this->onCreate($form, $values);
                } else {
                    $this->onUpdate($form, $values, $row);
                }
            } catch (Throwable $exception) {
                if (Debugger::isEnabled()
                    || $exception instanceof AbortException
                    || $exception instanceof BadRequestException) {
                    throw $exception;
                }
                Debugger::log($exception, ILogger::EXCEPTION);
                $form->addError('Request failed, please try again later.');
            }
        };
        return new NeoFormControl($form, $this->templateFile(), $this->templateVars($form, $row));
    }
}
