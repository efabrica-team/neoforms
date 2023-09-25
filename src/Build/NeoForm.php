<?php

namespace Efabrica\NeoForms\Build;

use Efabrica\NeoForms\Control\ControlGroupBuilder;
use Efabrica\NeoForms\Control\NeoControlGroup;
use Efabrica\NeoForms\Render\Template\NeoFormTemplate;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use Nette\Application\UI\Template;
use Nette\Forms\ControlGroup;
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

    private ?NeoFormTemplate $template = null;

    private static array $excludedKeys = [];

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

    public function getTemplate(): ?NeoFormTemplate
    {
        return $this->template;
    }

    public function setTemplate(?NeoFormTemplate $template): self
    {
        $this->template = $template;
        return $this;
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

    public function getValues($returnType = null, ?array $controls = null)
    {
        $values = parent::getValues($returnType, $controls);
        self::removeExcludedKeys($values, self::$excludedKeys);
        return $values;
    }

    public static function addExcludedKeys(string ...$keys): void
    {
        foreach ($keys as $key) {
            self::$excludedKeys[$key] = $key;
        }
    }

    /**
     * @param array|object $values
     * @return void
     */
    public static function removeExcludedKeys(&$values): void
    {
        foreach ($values as $key => &$value) {
            if (is_object($value) || is_array($value)) {
                self::removeExcludedKeys($value);
            } elseif (isset(self::$excludedKeys[$key])) {
                if (is_object($values)) {
                    unset($values->$key);
                } else {
                    unset($values[$key]);
                }
            }
        }
    }

    /**
     * @param string|true|HtmlStringable|null $label
     *      true = same as name
     *      null = no label
     *      HtmlStringable = custom Html (Html::el())
     *      string = custom label with default Html
     */
    public function group(?string $name = null, ?string $class = null, $label = true): ControlGroupBuilder
    {
        if ($name !== null) {
            $group = $this->getGroup($name);
        }
        $group ??= $this->addGroup($name, false);
        $builder = new ControlGroupBuilder($this, $group);
        $builder->setClass($class)->setLabel($label === true ? $name : $label);
        return $builder;
    }

    public function addButton(string $name, $caption = null, ?string $icon = null): Button
    {
        return parent::addButton($name, $caption)->setOption('icon', $icon);
    }
}
