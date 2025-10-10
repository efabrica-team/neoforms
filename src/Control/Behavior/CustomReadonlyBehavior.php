<?php

namespace Efabrica\NeoForms\Control\Behavior;

interface CustomReadonlyBehavior
{
    public function setReadonly(bool $readonly): self;
}
