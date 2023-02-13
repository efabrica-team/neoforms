<?php

namespace Efabrica\NeoForms\Build;

use Nette\Forms\Container;
use Nette\Forms\Controls\MultiSelectBox;
use Nette\Forms\Controls\SelectBox;

class NeoContainer extends Container
{
    use NeoContainerTrait;

    public function getForm(bool $throw = true): NeoForm
    {
        $form = $this->lookup(NeoForm::class, $throw);
        assert($form instanceof NeoForm);
        return $form;
    }

    public function addSelect(string $name, $label = null, ?array $items = null, ?int $size = null): SelectBox
    {
        return parent::addSelect($name, $label, $items, $size)->checkDefaultValue(false);
    }

    public function addMultiSelect(string $name, $label = null, ?array $items = null, ?int $size = null): MultiSelectBox
    {
        return parent::addMultiSelect($name, $label, $items, $size)->checkDefaultValue(false);
    }
}
