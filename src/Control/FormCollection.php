<?php

namespace Efabrica\NeoForms\Control;

use Closure;
use Efabrica\NeoForms\Build\NeoContainer;
use Nette\ComponentModel\IComponent;
use Nette\Forms\Container;
use Nette\Forms\Controls\BaseControl;
use Nette\InvalidArgumentException;
use Nette\Utils\ArrayHash;
use Traversable;

class FormCollection extends NeoContainer
{
    public const PROTOTYPE = '__prototype__';

    private string $label;

    /**
     * @var Closure(NeoContainer): (void|mixed)
     */
    private Closure $formFactory;

    private NeoContainer $prototype;

    private ?string $componentTemplate = null;

    private ?string $collectionTemplate = null;

    /**
     * @param string                               $label
     * @param callable(NeoContainer): (void|mixed) $formFactory
     */
    public function __construct(string $label, callable $formFactory)
    {
        $this->label = $label;
        $this->formFactory = Closure::fromCallable($formFactory);
        $this->prototype = $this->addContainer(self::PROTOTYPE);
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

    public function validate(?array $controls = null): void
    {
        $this->removeComponent($this->prototype);
        parent::validate($controls);
        $this->addComponent($this->prototype, self::PROTOTYPE);
    }

    /**
     * @param iterable|object $data
     * @return $this
     */
    public function setValues($data, bool $erase = false): self
    {
        /** @var mixed $data */
        if ($data instanceof Traversable) {
            $values = iterator_to_array($data);
        } elseif (is_object($data) || is_array($data) || $data === null) {
            $values = (array)$data;
        } else {
            throw new InvalidArgumentException(sprintf('First parameter must be iterable, %s given.',
                is_object($data) ? get_class($data) : gettype($data)
            ));
        }
        $components = iterator_to_array($this->getComponents());
        foreach ($values as $key => $_) {
            if (!isset($components[$key])) {
                $this->formFactory->__invoke($this->addContainer($key));
            }
            unset($components[$key]);
        }
        foreach ($components as $key => $_) {
            if (!isset($values[$key]) && $key !== self::PROTOTYPE) {
                $this->removeComponent($this->getComponent((string)$key));
            }
        }

        parent::setValues($data, $erase);
        return $this;
    }

    public function getUntrustedValues($returnType = ArrayHash::class, ?array $controls = null)
    {
        $this->removeComponent($this->prototype);
        $values = parent::getUntrustedValues($returnType, $controls);
        $this->addComponent($this->prototype, self::PROTOTYPE);
        return $values;
    }

    /**
     * @return (BaseControl|Container)[]
     */
    public function getItems(): iterable
    {
        foreach ($this->getComponents() as $component) {
            if ($component === $this->prototype) {
                continue;
            }
            if ($component instanceof BaseControl || $component instanceof Container) {
                yield $component;
            }
        }
    }
}
