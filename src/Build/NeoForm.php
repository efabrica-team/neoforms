<?php

namespace Efabrica\NeoForms\Build;

use Efabrica\NeoForms\Control\ControlGroupBuilder;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use Nette\Application\UI\Template;
use Nette\Localization\Translator;
use Throwable;
use Tracy\Debugger;
use Tracy\ILogger;

/**
 * @method Presenter getPresenter()
 */
class NeoForm extends Form
{
    public const OPT_RTE = 'rteRegistrator';

    private bool $readonly = false;
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

    /**
     * @param scalar[]|scalar $redirectArgs
     */
    public function finish(?string $flashMessage = null, string $redirect = 'default', $redirectArgs = []): void
    {
        $presenter = $this->getPresenter();
        if ($flashMessage !== null) {
            $translator = $this->getTranslator();
            assert($translator instanceof Translator);
            $presenter->flashMessage($translator->translate($flashMessage), 'success');
        }
        $presenter->redirect($redirect, $redirectArgs);
    }

    /**
     * @return $this
     */
    public function setOnSuccess(callable $onSuccess): self
    {
        $fn = static function (NeoForm $form, array $values) use ($onSuccess) {
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
        /** @var callable $fn */
        $this->onSuccess[] = $fn;

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

    /**
     * @return NeoForm to fool the IDE into seeing all ->add*() methods
     */
    public function group(?string $name = null, ?string $class = null)
    {
        /** @var NeoForm $group */
        $group = new ControlGroupBuilder($this, $class ?? 'c-form', $name);
        return $group;
    }

    /**
     * @return NeoForm to fool the IDE into seeing all ->add*() methods
     */
    public function row(?string $name = null)
    {
        return $this->group($name, 'row');
    }

    /**
     * @return NeoForm to fool the IDE into seeing all ->add*() methods
     */
    public function col(?string $col = null, ?string $name = null)
    {
        return $this->group($name, 'col' . (trim((string)$col) === '' ? '' : '-') . $col);
    }
}
