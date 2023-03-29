<?php

namespace Efabrica\NeoForms\Control;

use Nette\Forms\Controls\MultiSelectBox as NetteMultiSelectBox;

/**
 * @method __construct(string|object $label = null, ?array $items = null)
 */
class MultiSelectBox extends NetteMultiSelectBox
{
    use SelectBoxTrait;
}
