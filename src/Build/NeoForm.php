<?php

namespace Efabrica\NeoForms\Build;

use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Application\UI\Template;
use Throwable;
use Tracy\Debugger;
use Tracy\ILogger;

class NeoForm extends Form
{
    private bool $readonly = false;

    private array $options = [];

    use NeoContainerTrait;

    /**
     * @return $this
     */
    public function setReadonly(bool $readonly = true): self
    {
        $this->readonly = $readonly;
        return $this;
    }

    public function isReadonly(): bool
    {
        return $this->readonly;
    }

    public function getOption(string $name)
    {
        return $this->options[$name] ?? null;
    }

    /**
     * @return $this
     */
    public function setOption(string $name, $value): self
    {
        $this->options[$name] = $value;
        return $this;
    }

    /**
     * @param scalar[]|scalar $redirectArgs
     */
    public function finish(?string $flashMessage = null, string $redirect = 'default', $redirectArgs = []): void
    {
        $presenter = $this->getPresenter();
        assert($presenter instanceof Control);
        if ($flashMessage !== null) {
            $presenter->flashMessage($this->getTranslator()->translate($flashMessage), 'success');
        }
        $presenter->redirect($redirect, $redirectArgs);
    }

    /**
     * @return $this
     */
    public function setOnSuccess(callable $onSuccess): self
    {
        $this->onSuccess[] = static function (NeoForm $form, array $values) use ($onSuccess) {
            if ($form->isReadonly()) {
                return; // there is no submit button if the form is readonly
            }
            try {
                $onSuccess($form, $values);
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
        return $this;
    }

    public function withTemplate(string $templatePath, array $args = []): NeoFormControl
    {
        return new NeoFormControl($this, function (Template $template) use ($templatePath, $args) {
            $template->setFile($templatePath);
            foreach ($args as $key => $value) {
                $template->$key = $value;
            }
        });
    }
}
