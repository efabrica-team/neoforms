<?php

namespace Efabrica\NeoForms\Build;

use Efabrica\NeoForms\Control\ControlGroupBuilder;
use Efabrica\NeoForms\Render\Template\NeoFormTemplate;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use Nette\Application\UI\Template;
use Nette\Forms\Controls\Button;
use Nette\HtmlStringable;
use Nette\Localization\Translator;
use Stringable;
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
    private bool $readonlyAttr = false;

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

    /**
     * Set form to return fields with attribute readonly
     * @return $this
     */
    public function setReadonlyAttr(bool $readonlyAttr = true): self
    {
        $this->readonlyAttr = $readonlyAttr;
        return $this;
    }

    public function isReadonlyAttr(): bool
    {
        return $this->readonlyAttr;
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

    public function finish(?string $flashMessage = null, ?string $redirect = 'default', array $redirectArgs = []): void
    {
        $presenter = $this->getPresenter();
        if ($flashMessage !== null) {
            $translator = $this->getTranslator();
            assert($translator instanceof Translator);
            $presenter->flashMessage($translator->translate($flashMessage), 'success');
        }
        if ($redirect !== null) {
            $presenter->redirect($redirect, $redirectArgs);
        }
    }

    /**
     * @return $this
     */
    public function setOnSuccess(callable $onSuccess): self
    {
        $fn = static function (NeoForm $form, array $values) use ($onSuccess) {
            if ($form->isReadonly() || $form->isReadonlyAttr()) {
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

    public function getValues(string|object|bool|null $returnType = null, ?array $controls = null): object|array
    {
        $values = parent::getValues($returnType, $controls);
        self::removeExcludedKeys($values);
        return $values;
    }

    public static function addExcludedKeys(string ...$keys): void
    {
        foreach ($keys as $key) {
            self::$excludedKeys[$key] = $key;
        }
    }

    /**
     * @param iterable|object $values
     * @return void
     */
    public static function removeExcludedKeys(iterable|object &$values): void
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
    public function group(?string $name = null, ?string $class = null, bool $label = true): ControlGroupBuilder
    {
        if ($name !== null) {
            $group = $this->getGroup($name);
        }
        $group ??= $this->addGroup($name, false);
        $builder = new ControlGroupBuilder($this, $group);
        $builder->setClass($class)->setLabel($label === true ? $name : $label);
        return $builder;
    }

    public function addButton(string $name, string|Stringable|null $caption = null, ?string $icon = null): Button
    {
        return parent::addButton($name, $caption)->setOption('icon', $icon);
    }
}
