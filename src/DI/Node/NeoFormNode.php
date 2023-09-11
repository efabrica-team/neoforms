<?php

namespace Efabrica\NeoForms\DI\Node;

use Efabrica\NeoForms\Build\NeoFormControl;
use Latte\Compiler\Nodes\Php\Scalar\StringNode;
use Latte\Compiler\PrintContext;
use Nette\Bridges\FormsLatte\Nodes\FormNode;

class NeoFormNode extends FormNode
{
    public function print(PrintContext $context): string
    {
        return $context->format(
            '$form = $this->global->formsStack[] = '
            . ($this->name instanceof StringNode
                ? '$this->global->uiControl[%0.node]'
                : 'is_object($ʟ_tmp = %0.node) ? $ʟ_tmp : $this->global->uiControl[$ʟ_tmp]')
            . ' %1.line;'
            . 'if ($form instanceof ' . NeoFormControl::class . ') $form = $form->form;'
            . 'echo $this->global->neoFormRenderer->formStart($this->global->formsStack[] = $form, %2.node); %1.line'
            . ' %3.node '
            . 'echo $this->global->neoFormRenderer->formEnd(array_pop($this->global->formsStack), %2.node);'
            . " %4.line;\n\n",
            $this->name,
            $this->position,
            $this->attributes,
            $this->content,
            $this->endLine,
        );
    }
}
