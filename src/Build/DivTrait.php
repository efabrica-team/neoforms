<?php

namespace Efabrica\NeoForms\Build;

use Efabrica\NeoForms\Control\ControlGroupBuilder;

trait DivTrait
{
    public function row(?string $name = null): ControlGroupBuilder
    {
        return $this->group($name, 'row', null);
    }

    public function col(?string $col = null, ?string $name = null): ControlGroupBuilder
    {
        return $this->group($name, 'col' . (trim((string)$col) === '' ? '' : '-') . $col, null);
    }
}
