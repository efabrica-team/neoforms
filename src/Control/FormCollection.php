<?php

namespace Efabrica\NeoForms\Control;

use Closure;
use Efabrica\NeoForms\Build\NeoContainer;
use Efabrica\NeoForms\Build\NeoForm;
use Efabrica\NeoForms\Render\NeoFormRenderer;
use JsonException;
use Nette\Bridges\ApplicationLatte\Template;
use Nette\Bridges\ApplicationLatte\TemplateFactory;
use Nette\ComponentModel\IComponent;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;
use Nette\InvalidArgumentException;
use Nette\Utils\ArrayHash;
use Nette\Utils\Html;
use RuntimeException;
use Traversable;

class FormCollection extends NeoContainer
{
    public const ORIGINAL_DATA = '__neoFC_originalData__';

    private static int $prototypeIndex = 0;

    private string $label;

    /**
     * @var Closure(FormCollectionItem): (void|mixed)
     */
    private Closure $formFactory;

    protected FormCollectionItem $prototype;

    protected ?string $componentTemplate = null;

    protected ?string $collectionTemplate = null;

    private ?bool $simple = null;

    private ?array $httpData = null;

    private int $requiredCount = 0;

    /**
     * @param string                                     $label
     * @param callable(FormCollectionItem): (void|mixed) $formFactory
     */
    public function __construct(string $label, callable $formFactory)
    {
        $this->setSingleRender(true);
        $this->label = $label;
        $this->formFactory = Closure::fromCallable($formFactory);
        $this->prototype = $this->addCollectionItem('__prototype' . ++self::$prototypeIndex . '__', true);
        $this->formFactory->__invoke($this->prototype);
        NeoForm::addExcludedKeys(self::ORIGINAL_DATA, FormCollectionItem::UNIQID, $this->prototype->name);
    }

    public function getDiff(): FormCollectionDiff
    {
        $httpData = $this->getHttpData();
        if ($httpData === null) {
            throw new RuntimeException('FormCollection::getDiff() called before form is submitted.');
        }
        return new FormCollectionDiff($httpData);
    }

    public function getLabel(): string
    {
        $translator = $this->getForm()->getTranslator();
        if ($translator === null) {
            return $this->label;
        }
        return $translator->translate($this->label);
    }

    public function getComponentTemplate(): ?string
    {
        return $this->componentTemplate;
    }

    public function setComponentTemplate(?string $componentTemplate): void
    {
        $this->componentTemplate = $componentTemplate;
    }

    public function getCollectionTemplate(): ?string
    {
        return $this->collectionTemplate;
    }

    public function setCollectionTemplate(?string $collectionTemplate): void
    {
        $this->collectionTemplate = $collectionTemplate;
    }

    public function getPrototype(): NeoContainer
    {
        return $this->prototype;
    }

    protected function getHttpData(): ?array
    {
        if ($this->httpData !== null) {
            return $this->httpData;
        }
        if ($this->getForm()->isSubmitted() === false) {
            return null;
        }

        /** @var array $httpData */
        $httpData = $this->getForm()->getHttpData();
        /** @var string $lookupPath */
        $lookupPath = $this->lookupPath(NeoForm::class);
        foreach (explode(IComponent::NameSeparator, $lookupPath) as $path) {
            $httpData = $httpData[$path] ?? null;
        }

        return $httpData;
    }

    public function validate(?array $controls = null): void
    {
        $this->updateChildren($this->getHttpData());
        $this->removeComponent($this->prototype);
        parent::validate($controls);
        $this->addComponent($this->prototype, $this->prototype->name);
    }

    public function setValues($data, bool $erase = false)
    {
        /** @var mixed $data */
        if ($data === null || is_iterable($data)) {
            $this->updateChildren($data);
        }
        /** @var array|object $data */
        return parent::setValues($data, $erase);
    }

    public function updateChildren(?iterable $data = null): void
    {
        $data ??= [];
        /** @var mixed $data */
        if ($data instanceof Traversable) {
            $values = iterator_to_array($data);
        } elseif (is_object($data) || is_array($data) || $data === null) {
            $values = (array)$data;
        } else {
            throw new InvalidArgumentException(sprintf(
                'First parameter must be iterable, %s given.',
                is_object($data) ? get_class($data) : gettype($data)
            ));
        }
        $components = iterator_to_array($this->getComponents());
        foreach ($values as $key => $childValues) {
            if ($key === self::ORIGINAL_DATA) {
                continue;
            }
            if (!isset($components[$key])) {
                $child = $this->addCollectionItem($key);
                $this->formFactory->__invoke($child);
                $child->setValues($childValues);
            }
            unset($components[$key]);
        }
        foreach ($components as $key => $_) {
            if (!isset($values[$key]) && $_ !== $this->prototype) {
                $this->removeComponent($this->getComponent((string)$key));
            }
        }
    }

    /**
     * @return FormCollectionItem[]
     */
    public function getItems(): iterable
    {
        foreach ($this->getComponents() as $component) {
            if ($component === $this->prototype) {
                continue;
            }
            if ($component instanceof FormCollectionItem) {
                yield $component;
            }
        }
    }

    public function isSimple(): bool
    {
        if ($this->simple !== null) {
            return $this->simple;
        }
        foreach ($this->prototype->getComponents() as $input) {
            if ($input instanceof BaseControl && $input->getCaption() !== null && $input->getCaption() !== '') {
                return false;
            }
        }
        return true;
    }

    public function forceSimple(?bool $simple): void
    {
        $this->simple = $simple;
    }

    protected function renderTemplate(string $path, array $params): Html
    {
        $presenter = $this->getForm()->getPresenter();
        $templateFactory = $presenter->getTemplateFactory();
        assert($templateFactory instanceof TemplateFactory);
        $template = $templateFactory->createTemplate($presenter, Template::class);
        assert($template instanceof Template);
        return Html::fromHtml($template->renderToString($path, $params));
    }

    /**
     * @throws JsonException
     */
    public function getHtml(NeoFormRenderer $renderer): Html
    {
        $collectionTemplate = $this->getCollectionTemplate();
        $addButton = $this->getAddButtonHtml($renderer);
        if ($collectionTemplate !== null) {
            return $this->renderTemplate($collectionTemplate, ['collection' => $this, 'addBtn' => $addButton]);
        }
        $collDiv = Html::el('div')->class('form-collection')
            ->setAttribute('data-proto', $this->getItemHtml($renderer, $this->prototype))
            ->setAttribute('data-proto-name', $this->prototype->getName())
            ->setAttribute('data-required-count', $this->requiredCount)
        ;
        $itemsDiv = Html::el('div')->class('form-collection-items');
        foreach ($this->getItems() as $item) {
            $itemsDiv->addHtml($this->getItemHtml($renderer, $item));
        }
        $originalData = $this->addHidden(self::ORIGINAL_DATA, json_encode($this->getUntrustedValues('array'), JSON_THROW_ON_ERROR));
        $el = Html::el()
            ->addHtml($this->getLabelHtml())
            ->addHtml($collDiv->addHtml($itemsDiv)->addHtml($addButton))
            ->addHtml($originalData->getControl())
        ;
        $this->removeComponent($originalData);
        return $el;
    }

    public function getItemHtml(NeoFormRenderer $renderer, FormCollectionItem $item): Html
    {
        $componentTemplate = $this->getComponentTemplate();
        if ($componentTemplate !== null) {
            return $this->renderTemplate($componentTemplate, ['collection' => $this, 'item' => $item]);
        }
        $isSimple = $this->isSimple();
        return Html::el('div')
            ->class('form-collection-item')
            ->class('form-collection-item-simple', $isSimple)
            ->class('form-collection-item-multi', !$isSimple)
            ->addHtml(
                Html::el('div')->class('form-collection-item-form')
                    ->addHtml($renderer->container($item))
            )
            ->addHtml(
                $this->getRemoveButtonHtml()
            )
        ;
    }

    public function getAddButtonHtml(NeoFormRenderer $renderer): Html
    {
        return Html::el('div')->class('form-collection-actions')
            ->addHtml(
                Html::el('a')->href('javascript:')
                    ->class('form-collection-add', true)
                    ->addHtml('+')
            )
        ;
    }

    public function getRemoveButtonHtml(): Html
    {
        return Html::el('div')->class('form-collection-item-actions')
            ->addHtml(
                Html::el('a')->href('javascript:')
                    ->class('form-collection-item-remove', true)
                    ->class('btn btn-danger', true)
                    ->addHtml('-')
            )
        ;
    }

    public function getLabelHtml(): Html
    {
        return Html::el('label', $this->getLabel());
    }

    /**
     * @param int|string $key
     */
    protected function addCollectionItem($key, bool $prototype = false): FormCollectionItem
    {
        $item = new FormCollectionItem($prototype);
        $this->addContainer($key, $item);
        return $item;
    }

    public function getUntrustedValues($returnType = ArrayHash::class, ?array $controls = null)
    {
        $this->removeComponent($this->prototype);
        $values = parent::getUntrustedValues($returnType, $controls);
        $this->addComponent($this->prototype, $this->prototype->name);
        return $values;
    }

    public function setRequiredCount(int $count = 0): void
    {
        $this->requiredCount = $count;
    }
}
