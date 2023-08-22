<?php

namespace Efabrica\NeoForms\Control;

use Closure;
use Efabrica\NeoForms\Build\NeoContainer;
use Nette\ComponentModel\IComponent;
use Nette\InvalidArgumentException;
use Nette\Utils\ArrayHash;
use Traversable;

class FormCollection extends NeoContainer
{
    public const PROTOTYPE = '__prototype__';
    private string $label;
    /**
     * @var Closure(NeoContainer): void
     */
    private Closure $formFactory;
    private NeoContainer $prototype;

    private ?string $componentTemplate = null;
    private ?string $collectionTemplate = null;

    public function __construct(string $label, callable $formFactory)
    {
        $this->label = $label;
        $this->formFactory = Closure::fromCallable($formFactory);
        $this->prototype = $this->addContainer(self::PROTOTYPE);
        $this->formFactory->__invoke($this->prototype);
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getPrototype(): NeoContainer
    {
        return $this->prototype;
    }

    /**
     * @param iterable|object $data
     * @return $this
     */
    public function setValues($data, bool $erase = false): self
    {
        if ($data instanceof Traversable) {
            $values = iterator_to_array($data);
        } elseif (is_object($data) || is_array($data) || $data === null) {
            $values = (array) $data;
        } else {
            throw new InvalidArgumentException(sprintf('First parameter must be iterable, %s given.', gettype($data)));
        }
        $components = iterator_to_array($this->getComponents());
        foreach ($values as $key => $_) {
            if (!isset($components[$key])) {
                $this->formFactory->__invoke($this->addContainer($key));
            }
            unset($components[$key]);
        }
        foreach ($components as $key => $_) {
            if (!isset($values[$key])) {
                $this->removeComponent($this->getComponent($key));
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
}
