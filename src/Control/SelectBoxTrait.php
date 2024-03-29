<?php

namespace Efabrica\NeoForms\Control;

trait SelectBoxTrait
{
    private array $itemTree = [];

    /**
     * @param string|object|null $label
     */
    public function __construct($label = null, ?array $items = null)
    {
        if ($items !== null) {
            $this->itemTree = $items;
        }
        parent::__construct($label, $items);
    }

    public function setItems(array $items, bool $useKeys = true): self
    {
        $this->itemTree = $items;
        if (!$useKeys) {
            $this->itemTree = array_combine($items, $items);
        }
        return parent::setItems($items, $useKeys);
    }

    public function getItems(bool $tree = false): array
    {
        if ($tree) {
            return $this->itemTree;
        }
        return parent::getItems();
    }
}
