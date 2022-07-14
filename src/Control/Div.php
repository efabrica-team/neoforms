<?php

namespace Efabrica\NeoForms\Control;

use Nette\Forms\Container;

class Div extends Container
{
    protected array $attrs = [];

    public function __construct(string $class = '')
    {
        $this->setClass($class);
    }

    public function getClass(): string
    {
        return $this->attrs['class'] ?? "";
    }

    /**
     * @return $this
     */
    public function setClass(string $class): self
    {
        $this->attrs['class'] = $class;
        return $this;
    }

    public function getAttrs(): array
    {
        return $this->attrs;
    }

    /**
     * @return $this
     */
    public function setAttrs(array $attrs): self
    {
        $this->attrs = $attrs;
        return $this;
    }

    /**
     * @param string $name
     * @param scalar $value
     * @return $this
     */
    public function setAttr(string $name, $value = true): self
    {
        $this->attrs[$name] = $value;
        if ($value === false) {
            unset($this->attrs[$name]);
        }
        return $this;
    }
}
