<?php

namespace Efabrica\NeoForms\Build;

use Efabrica\NeoForms\Control\ControlGroupBuilder;
use Efabrica\NeoForms\Control\FormCollection;
use Efabrica\NeoForms\Control\FormCollectionItem;
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

    private bool $viewMode = false;

    private bool $readonlyInputs = false;

    private ?NeoFormTemplate $template = null;

    /**
     * Extra value keys to strip from getValues() output, scoped to this form instance.
     * FormCollection's own bookkeeping keys are always stripped regardless of this list.
     *
     * @var array<string, string>
     */
    private array $excludedKeys = [];

    /**
     * View mode: render every field as plain read-only text (no inputs, no submit button)
     * instead of editable controls. This is the "say goodbye to grayed-out disabled fields"
     * mode - use it to show a form to someone who may not edit it.
     *
     * @return $this
     */
    public function setViewMode(bool $viewMode = true): self
    {
        $this->viewMode = $viewMode;
        return $this;
    }

    public function isViewMode(): bool
    {
        return $this->viewMode;
    }

    /**
     * Keep rendering editable controls, but add the HTML `readonly` attribute to each input.
     *
     * @return $this
     */
    public function setReadonlyInputs(bool $readonlyInputs = true): self
    {
        $this->readonlyInputs = $readonlyInputs;
        return $this;
    }

    public function hasReadonlyInputs(): bool
    {
        return $this->readonlyInputs;
    }

    /**
     * @deprecated Use setViewMode() instead. Renders fields as plain text (view mode).
     * @return $this
     */
    public function setReadonly(bool $readonly = true): self
    {
        return $this->setViewMode($readonly);
    }

    /**
     * @deprecated Use isViewMode() instead.
     */
    public function isReadonly(): bool
    {
        return $this->isViewMode();
    }

    /**
     * @deprecated Use setReadonlyInputs() instead. Adds the HTML `readonly` attribute to inputs.
     * @return $this
     */
    public function setReadonlyAttr(bool $readonlyAttr = true): self
    {
        return $this->setReadonlyInputs($readonlyAttr);
    }

    /**
     * @deprecated Use hasReadonlyInputs() instead.
     */
    public function isReadonlyAttr(): bool
    {
        return $this->hasReadonlyInputs();
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
     * @param array<int|string, mixed> $redirectArgs
     */
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
            if ($form->isViewMode() || $form->hasReadonlyInputs()) {
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

    /**
     * @param array<string, mixed> $args
     */
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
     * @return array<string, mixed>|object
     */
    public function getValues(string|object|null $returnType = null, ?array $controls = null): object|array
    {
        $values = parent::getValues($returnType, $controls);
        self::removeExcludedKeys($values, $this->excludedKeys);
        return $values;
    }

    /**
     * Register extra value keys to strip from this form's getValues() output.
     * @return $this
     */
    public function addExcludedKeys(string ...$keys): self
    {
        foreach ($keys as $key) {
            $this->excludedKeys[$key] = $key;
        }
        return $this;
    }

    /**
     * Recursively strips FormCollection's internal bookkeeping keys (originalData, uniqId)
     * plus any caller-supplied $excludedKeys from a values structure.
     *
     * @param iterable<array-key, mixed>|object $values
     * @param array<array-key, string> $excludedKeys
     */
    public static function removeExcludedKeys(iterable|object &$values, array $excludedKeys = []): void
    {
        $excluded = [
            FormCollection::ORIGINAL_DATA => FormCollection::ORIGINAL_DATA,
            FormCollectionItem::UNIQID => FormCollectionItem::UNIQID,
        ];
        foreach ($excludedKeys as $key) {
            $excluded[$key] = $key;
        }
        // @phpstan-ignore-next-line foreach.nonIterable (plain objects, e.g. custom getValues() DTOs, are iterable over their public properties at runtime)
        foreach ($values as $key => &$value) {
            if (is_object($value) || is_array($value)) {
                self::removeExcludedKeys($value, $excludedKeys);
            } elseif (isset($excluded[$key])) {
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
    public function group(?string $name = null, ?string $class = null, string|true|HtmlStringable|null $label = true): ControlGroupBuilder
    {
        // reuse scope: form-level named groups (visible to getGroups()/{formRest})
        if ($name !== null) {
            $group = $this->getGroup($name);
        }
        $group ??= $this->addGroup($name, false);
        return ControlGroupBuilder::create($this, $group, $name, $class, $label);
    }

    public function addButton(string $name, string|Stringable|null $caption = null, ?string $icon = null): Button
    {
        return parent::addButton($name, $caption)->setOption('icon', $icon);
    }
}
