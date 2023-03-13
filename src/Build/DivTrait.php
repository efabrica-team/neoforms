<?php

namespace Efabrica\NeoForms\Build;

trait DivTrait
{
    /**
     * @return NeoForm
     */
    public function row(?string $name = null)
    {
        /** @var NeoForm $group */
        $group = $this->group($name, 'row', null);
        return $group;
    }

    /**
     * @return NeoForm
     */
    public function col(?string $col = null, ?string $name = null)
    {
        /** @var NeoForm $group */
        $group = $this->group($name, 'col' . (trim((string)$col) === '' ? '' : '-') . $col, null);
        return $group;
    }
}