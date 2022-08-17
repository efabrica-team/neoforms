<?php

namespace Efabrica\NeoForms\Build;

use Nette\Application\BadRequestException;
use Nette\Application\UI\Control;
use Nette\Database\Table\ActiveRow;
use Throwable;
use Tracy\Debugger;
use Tracy\ILogger;
use Nette\Application\AbortException;

abstract class AbstractForm extends Control
{
    private NeoFormFactory $formFactory;
    protected ?ActiveRow $row;

    public function __construct(NeoFormFactory $formFactory, ?ActiveRow $row = null)
    {
        $this->formFactory = $formFactory;
        $this->row = $row;
    }

    public function render(): void
    {
        $form = $this->getComponent('form');
        assert($form instanceof NeoForm);
        $form->render();
    }

    public function createComponentForm(): NeoForm
    {
        $form = $this->formFactory->create();
        $this->buildForm($form, $this->row);
        if ($this->row !== null) {
            $form->setDefaults($this->initFormData($this->row));
        }
        $form->onSuccess[] = function (NeoForm $form, array $values) {
            try {
                $this->onSuccess($form, $values);
                if ($this->row === null) {
                    $this->onCreate($form, $values);
                } else {
                    $this->onUpdate($form, $values, $this->row);
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
        return $form;
    }

    abstract protected function buildForm(NeoForm $form, ?ActiveRow $row): void;

    abstract protected function initFormData(ActiveRow $row): array;

    protected function onSuccess(NeoForm $form, array $values): void
    {
    }

    protected function onCreate(NeoForm $form, array $values): void
    {
    }

    protected function onUpdate(NeoForm $form, array $values, ActiveRow $row): void
    {
    }

    /**
     * @param scalar[]|scalar $redirectArgs
     * @throws AbortException
     */
    protected function finish(string $flashMessage, string $redirect = 'default', $redirectArgs = []): void
    {
        $presenter = $this->getPresenter();
        assert($presenter instanceof Control);
        $presenter->flashMessage($this->translate($flashMessage), 'success');
        $presenter->redirect($redirect, $redirectArgs);
    }

    protected function translate(string $message, ...$parameters): string
    {
        return $this->formFactory->getTranslator()->translate($message, ...$parameters);
    }
}
