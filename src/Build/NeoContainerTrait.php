<?php

namespace Efabrica\NeoForms\Build;

use Closure;
use Efabrica\NeoForms\Control\CodeEditor;
use Efabrica\NeoForms\Control\FormCollection;
use Efabrica\NeoForms\Control\FormCollectionItem;
use Efabrica\NeoForms\Control\MultiSelectBox;
use Efabrica\NeoForms\Control\SelectBox;
use Efabrica\NeoForms\Control\StaticHtml;
use Efabrica\NeoForms\Control\StaticTags;
use Efabrica\NeoForms\Control\SubmitButton;
use Efabrica\NeoForms\Control\Tags;
use Efabrica\NeoForms\Control\ToggleSwitch;
use JetBrains\PhpStorm\ExpectedValues;
use Nette\Application\UI\Multiplier;
use Nette\Utils\Html;
use RadekDostal\NetteComponents\DateTimePicker\TbDatePicker;
use RadekDostal\NetteComponents\DateTimePicker\TbDateTimePicker;
use Stringable;

/**
 * @used-by \Efabrica\NeoForms\Build\NeoForm
 * @used-by \Efabrica\NeoForms\Build\NeoContainer
 */
trait NeoContainerTrait
{
    use DivTrait;

    /**
     * @var array<string, mixed>
     */
    protected array $options = [];

    /**
     * @return mixed|null
     */
    public function getOption(string $name)
    {
        return $this->options[$name] ?? null;
    }

    /**
     * @return array<string, mixed>
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param mixed $value
     * @return $this
     */
    public function setOption(string $name, $value): self
    {
        $this->options[$name] = $value;
        return $this;
    }

    private function addStatic(Html $html): StaticHtml
    {
        $component = new StaticHtml($html);
        $this->addComponent($component, '_static' . uniqid());
        return $component;
    }

    /**
     * @param array<string, mixed> $attrs
     */
    public function addHtml(string|Stringable $html, array $attrs = []): StaticHtml
    {
        $el = Html::el('div', $attrs);
        $el->addHtml($html instanceof Html ? $html : Html::fromHtml((string)$html));
        return $this->addStatic($el);
    }

    /**
     * @param int<1, 6> $level
     * @param array<string, mixed> $attrs
     */
    public function addHeading(string|Stringable $text, int $level = StaticHtml::DEFAULT_HEADING_LEVEL, array $attrs = []): StaticHtml
    {
        $level = max(1, min(6, $level));
        return $this->addStatic(Html::el('h' . $level, $attrs)->setText((string)$text));
    }

    /**
     * @param array<string, mixed> $attrs
     */
    public function addParagraph(string|Stringable $text, array $attrs = []): StaticHtml
    {
        return $this->addStatic(Html::el('p', $attrs)->setText((string)$text));
    }

    public function addLineBreak(int $count = 1): StaticHtml
    {
        $html = Html::el();
        for ($i = 0; $i < max(1, $count); $i++) {
            $html->addHtml(Html::el('br'));
        }
        return $this->addStatic($html);
    }

    /**
     * Vertical spacing of the given height (any CSS length, e.g. "1rem").
     */
    public function addSpace(string $height = '1rem'): StaticHtml
    {
        return $this->addStatic(Html::el('div', ['style' => 'height: ' . $height . ';', 'aria-hidden' => 'true']));
    }

    /**
     * Alert message with the given text and type.
     *
     * @param array<string, mixed> $attrs
     */
    public function addAlert(string|Stringable $text, string $type = 'info', array $attrs = []): StaticHtml
    {
        $attrs['class'] = trim(($attrs['class'] ?? '') . ' alert alert-' . $type);
        return $this->addStatic(Html::el('div', $attrs)->setText((string)$text));
    }

    /**
     * @param array<string, mixed> $attrs
     */
    public function addSeparator(array $attrs = []): StaticHtml
    {
        return $this->addStatic(Html::el('hr', $attrs));
    }

    public function addSelect(string $name, Stringable|string|null $label = null, ?array $items = null, ?int $size = null): SelectBox
    {
        return $this[$name] = (new SelectBox($label, $items))
            ->setHtmlAttribute('size', $size > 1 ? $size : null)
            ->checkDefaultValue(false)
        ;
    }

    public function addMultiSelect(string $name, Stringable|string|null $label = null, ?array $items = null, ?int $size = null): MultiSelectBox
    {
        return $this[$name] = (new MultiSelectBox($label, $items))
            ->setHtmlAttribute('size', $size > 1 ? $size : null)
            ->checkDefaultValue(false)
        ;
    }

    public function addToggleSwitch(string $name, Stringable|string|null $label = null): ToggleSwitch
    {
        $component = new ToggleSwitch($label);
        $this->addComponent($component, $name);
        return $component;
    }

    /**
     * @param array<string, mixed> $config
     */
    public function addTags(string $name, Stringable|string|null $label = null, array $config = [], ?string $placeholder = null): Tags
    {
        $component = new Tags($label, $config, $placeholder);
        $this->addComponent($component, $name);
        return $component;
    }

    /**
     * @param string[]|array{value: string}[] $choices
     */
    public function addStaticTags(
        string $name,
        Stringable|string|null $label,
        array $choices,
        bool $allowCustomTags = false,
        ?string $placeholder = null
    ): StaticTags {
        $component = new StaticTags($label, $choices, $allowCustomTags, $placeholder);
        $this->addComponent($component, $name);
        return $component;
    }

    public function addDatePicker(string $name, Stringable|string|null $label = null, ?int $maxLength = null): TbDatePicker
    {
        $component = new TbDatePicker($label, $maxLength);
        $this->addComponent($component, $name);
        return $component;
    }

    public function addDateTimePicker(string $name, Stringable|string|null $label = null, ?int $maxLength = null): TbDateTimePicker
    {
        $component = new TbDateTimePicker($label, $maxLength);
        $this->addComponent($component, $name);
        return $component;
    }

    public function addMultiplier(string $name, callable $factory): Multiplier
    {
        $component = new Multiplier($factory);
        $this->addComponent($component, $name);
        return $component;
    }

    /**
     * @param value-of<CodeEditor::MODES> $mode
     */
    public function addCodeEditor(
        string $name,
        #[ExpectedValues(CodeEditor::MODES)]
        string $mode,
        ?string $label = null
    ): CodeEditor {
        $component = new CodeEditor($mode, $label);
        $this->addComponent($component, $name);
        return $component;
    }

    /**
     * @param (Closure(SubmitButton, array<array-key, mixed>|object): void)|null $onSubmit
     */
    public function addSubmit(string $name, string|Stringable|null $caption = null, ?Closure $onSubmit = null): SubmitButton
    {
        $component = new SubmitButton($caption);
        if ($onSubmit !== null) {
            $component->onClick[] = $onSubmit;
        }
        $this->addComponent($component, $name);
        return $component;
    }

    public function addContainer(int|string $name, ?NeoContainer $container = null): NeoContainer
    {
        $control = $container ?? new NeoContainer();
        $control->currentGroup = $this->currentGroup;
        if ($this->currentGroup !== null) {
            $this->currentGroup->add($control);
        }

        $this->addComponent($control, is_int($name) ? "$name" : $name);
        return $control;
    }

    /**
     * @param callable(FormCollectionItem): (void|mixed) $factory
     */
    public function addCollection(string $name, string $label, callable $factory): FormCollection
    {
        $container = new FormCollection($label, $factory);
        $this->addContainer($name, $container);
        return $container;
    }
}
