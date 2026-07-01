<?php

namespace Efabrica\NeoForms\Control;

use Stringable;

trait SelectBoxTrait
{
    /**
     * @var array<int|string, mixed>
     */
    private array $itemTree = [];

    /**
     * @param string|Stringable|null $label
     * @param array<int|string, mixed>|null $items
     */
    public function __construct(string|Stringable|null $label = null, ?array $items = null)
    {
        if ($items !== null) {
            $this->itemTree = $items;
        }
        parent::__construct($label, $items);
    }

    /**
     * @param array<int|string, mixed> $items
     */
    public function setItems(array $items, bool $useKeys = true): self
    {
        $this->itemTree = $items;
        if (!$useKeys) {
            $this->itemTree = array_combine($items, $items);
        }
        return parent::setItems($items, $useKeys);
    }

    /**
     * @return array<int|string, mixed>
     */
    public function getItems(bool $tree = false): array
    {
        if ($tree) {
            return $this->itemTree;
        }
        return parent::getItems();
    }
}
