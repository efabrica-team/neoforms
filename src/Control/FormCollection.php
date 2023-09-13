<?php

namespace Efabrica\NeoForms\Control;

use Closure;
use Efabrica\NeoForms\Build\NeoContainer;
use Efabrica\NeoForms\Build\NeoForm;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;
use Nette\InvalidArgumentException;
use Nette\Utils\ArrayHash;
use Traversable;

class FormCollection extends NeoContainer
{
    private static int $prototypeIndex = 0;

    private string $label;

    /**
     * @var Closure(NeoContainer): (void|mixed)
     */
    private Closure $formFactory;

    private NeoContainer $prototype;

    private ?string $componentTemplate = null;

    private ?string $collectionTemplate = null;

    private ?bool $simple = null;

    /**
     * @param string                               $label
     * @param callable(NeoContainer): (void|mixed) $formFactory
     */
    public function __construct(string $label, callable $formFactory)
    {
        $this->label = $label;
        $this->formFactory = Closure::fromCallable($formFactory);
        $prototypeName = '__prototype' . ++self::$prototypeIndex . '__';
        $this->prototype = $this->addContainer($prototypeName);
        $this->formFactory->__invoke($this->prototype);
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
        $data = $this->getHttpData();
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
                $child = $this->addContainer($key);
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
     * @return NeoContainer[]
     */
    public function getItems(): iterable
    {
        foreach ($this->getComponents() as $component) {
            if ($component === $this->prototype) {
                continue;
            }
            if ($component instanceof NeoContainer) {
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
}
