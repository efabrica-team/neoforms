<?php

namespace Efabrica\NeoForms\Control;

use Closure;
use Efabrica\NeoForms\Build\NeoContainer;
use Efabrica\NeoForms\Build\NeoForm;
use Efabrica\NeoForms\Render\NeoFormRenderer;
use Nette\Bridges\ApplicationLatte\Template;
use Nette\Forms\Control;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;
use Nette\InvalidArgumentException;
use Nette\Utils\ArrayHash;
use Nette\Utils\Html;
use Traversable;

class FormCollection extends NeoContainer
{
    private static int $prototypeIndex = 0;

    private string $label;

    /**
     * @var Closure(NeoContainer): (void|mixed)
     */
    private Closure $formFactory;

    private FormCollectionItem $prototype;

    private ?string $componentTemplate = null;

    private ?string $collectionTemplate = null;

    private ?bool $simple = null;

    private ?array $httpData = null;

    /**
     * @param string                               $label
     * @param callable(NeoContainer): (void|mixed) $formFactory
     */
    public function __construct(string $label, callable $formFactory)
    {
        $this->label = $label;
        $this->formFactory = Closure::fromCallable($formFactory);
        $this->prototype = new FormCollectionItem(true);
        $this->addComponent($this->prototype, '__prototype' . ++self::$prototypeIndex . '__');
        $this->formFactory->__invoke($this->prototype);
    }

    public function getDiff(array $previousData): FormCollectionDiff
    {
        return new FormCollectionDiff($previousData, $this->getHttpData());
    }

    public function getLabel(): string
    {
        return $this->getForm()->getTranslator()->translate($this->label);
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
        if (!$this->getForm()->isSubmitted()) {
            return null;
        }
        $httpData = $this->getForm()->getHttpData();
        foreach (explode(Form::NameSeparator, $this->lookupPath(NeoForm::class)) as $path) {
            $httpData = $httpData[$path] ?? null;
        }
        return $httpData;
    }

    public function validate(?array $controls = null): void
    {
        $this->updateChildren();
        $this->removeComponent($this->prototype);
        parent::validate($controls);
        $this->addComponent($this->prototype, $this->prototype->getName());
    }

    public function updateChildren(): void
    {
        $data = $this->getHttpData() ?? $this->getValues();
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
            if (!isset($components[$key])) {
                $child = $this->addComponent(new FormCollectionItem(), $key);
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

    public function getUntrustedValues($returnType = ArrayHash::class, ?array $controls = null)
    {
        $this->removeComponent($this->prototype);
        $values = parent::getUntrustedValues($returnType, $controls);
        $this->addComponent($this->prototype, $this->prototype->getName());
        return $values;
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

    protected function renderTemplate(string $path, array $params)
    {
        return $this->getForm()->getPresenter()->getTemplateFactory()
            ->createTemplate($this->getForm()->getPresenter(), Template::class)
            ->renderToString($path, $params)
        ;
    }

    public function getHtml(NeoFormRenderer $renderer): Html
    {
        $collectionTemplate = $this->getCollectionTemplate();
        $addButton = $this->getAddButtonHtml($renderer);
        if ($collectionTemplate !== null) {
            return $this->renderTemplate($collectionTemplate, ['collection' => $this, 'addBtn' => $addButton]);
        }
        $el = Html::el('div')->class('form-collection');
        $items = Html::el('div')->class('form-collection-items');
        foreach ($this->getItems() as $item) {
            $items->addHtml($this->getItemHtml($renderer, $item));
        }
        return Html::el()
            ->addHtml(Html::el('label', $this->getLabel()))
            ->addHtml($el->addHtml($items)->addHtml($addButton))
        ;
    }

    public function getItemHtml(NeoFormRenderer $renderer, FormCollectionItem $item): Html
    {
        $componentTemplate = $this->getComponentTemplate();
        if ($componentTemplate !== null) {
            return $this->renderTemplate($componentTemplate, ['collection' => $this, 'item' => $item]);
        }
        $simple = $this->isSimple();
        return Html::el('div')
            ->class('form-collection-item')
            ->class('form-collection-item-simple', $simple)
            ->class('form-collection-item-multi', !$simple)
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
                    ->setAttribute('data-proto', $this->getItemHtml($renderer, $this->prototype))
                    ->setAttribute('data-proto-name', $this->prototype->getName())
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
}
