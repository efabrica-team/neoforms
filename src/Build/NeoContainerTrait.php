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
use Nette\HtmlStringable;
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

    private int $staticCount = 0;

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

    private function addStatic(StaticHtml $component, ?string $name): StaticHtml
    {
        $this->addComponent($component, $name ?? '_static' . ++$this->staticCount);
        return $component;
    }

    /**
     * Raw HTML. It is neither escaped nor translated.
     */
    public function addHtml(string|Stringable $html, ?string $name = null): StaticHtml
    {
        return $this->addStatic(
            new StaticHtml('html', $html instanceof HtmlStringable ? $html : Html::fromHtml((string)$html)),
            $name,
        );
    }

    public function addHeading(string|Stringable $text, int $level = StaticHtml::DEFAULT_HEADING_LEVEL, ?string $name = null): StaticHtml
    {
        return $this->addStatic(new StaticHtml('heading', $text), $name)->setOption('level', max(1, min(6, $level)));
    }

    public function addParagraph(string|Stringable $text, ?string $name = null): StaticHtml
    {
        return $this->addStatic(new StaticHtml('paragraph', $text), $name);
    }

    /**
     * @param string $type e.g. info, success, warning, danger; how it looks is up to the template
     */
    public function addAlert(string|Stringable $text, string $type = 'info', ?string $name = null): StaticHtml
    {
        return $this->addStatic(new StaticHtml('alert', $text), $name)->setOption('type', $type);
    }

    /**
     * @param string $size sm, md or lg (the template maps these), anything else is used as a CSS length (e.g. "1rem")
     */
    public function addSpace(string $size = 'md', ?string $name = null): StaticHtml
    {
        return $this->addStatic(new StaticHtml('space'), $name)->setOption('size', $size);
    }

    public function addSeparator(?string $name = null): StaticHtml
    {
        return $this->addStatic(new StaticHtml('separator'), $name);
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
