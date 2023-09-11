<?php

namespace Efabrica\NeoForms\DI;

use Efabrica\NeoForms\DI\Node\NeoFormNode;
use Efabrica\NeoForms\DI\Node\NeoFormUnpairedNode;
use Latte\Extension;

class NeoFormLatteExtension extends Extension
{
    public function getTags(): array
    {
        return [
            'neoForm' => [NeoFormNode::class, 'create'],
            'formRow' => [NeoFormUnpairedNode::class, 'createFormRow'],
            'formGroup' => [NeoFormUnpairedNode::class, 'createFormGroup'],
            'formInput' => [NeoFormUnpairedNode::class, 'createFormInput'],
            'formLabel' => [NeoFormUnpairedNode::class, 'createFormLabel'],
            'formErrors' => [NeoFormUnpairedNode::class, 'createFormErrors'],
            'formRest' => [NeoFormUnpairedNode::class, 'createFormRest'],
        ];
    }
}
