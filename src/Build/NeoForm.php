<?php

namespace Efabrica\NeoForms\Build;

use Efabrica\NeoForms\Control\ControlGroupBuilder;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use Nette\Application\UI\Template;
use Nette\Forms\Controls\Button;
use Nette\HtmlStringable;
use Nette\Localization\Translator;
use Throwable;
use Tracy\Debugger;
use Tracy\ILogger;

/**
 * @method Presenter getPresenter()
 */
class NeoForm extends Form
{
    use NeoContainerTrait;

    private bool $readonly = false;

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
     * @param array|scalar $redirectArgs
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
     * @param string|true|HtmlStringable|null $label
     *      true = same as name
     *      null = no label
     *      HtmlStringable = custom Html (Html::el())
     *      string = custom label with default Html
     * @return NeoForm to fool the static analysis into seeing all ->add*() methods
     */
    public function group(?string $name = null, ?string $class = null, $label = true)
    {
        if ($name !== null) {
            $group = $this->getGroup($name);
        }
        $group ??= $this->addGroup($name, false);
        $builder = new ControlGroupBuilder($this, $group);
        $builder->setClass($class)->setLabel($label === true ? $name : $label);
        /** @var NeoForm $builder */
        return $builder;
    }

    public function addButton(string $name, $caption = null, ?string $icon = null): Button
    {
        return parent::addButton($name, $caption)->setOption('icon', $icon);
    }
}
