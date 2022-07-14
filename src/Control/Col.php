<?php

namespace Efabrica\NeoForms\Control;

class Col extends Div
{
    public function __construct(string $class)
    {
        parent::__construct("col-$class");
    }
}
